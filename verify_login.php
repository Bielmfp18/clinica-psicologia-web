<?php
// VERIFY LOGIN

session_name('Mente_Renovada');
session_start();

require 'conn/conexao.php';   // espera prover $conn (PDO)
require_once 'helpers.php';   // fornece token_hash(), generate_token(), end_of_day_datetime(), cookie_expire_timestamp_end_of_day()

// Recebe parâmetros (GET ou POST)
$psicologo_id = isset($_GET['u']) ? (int)$_GET['u'] : (int)($_POST['u'] ?? 0);
$token = $_GET['t'] ?? $_POST['token'] ?? null;
$flash = null;

// Tempo padrão para token persistente (ajuste se quiser outro TTL)
$PERSISTENT_TTL_SECONDS = 60 * 60 * 24 * 30; // 30 dias

// Se recebeu token e usuário, tenta validar/consumir
if ($token && $psicologo_id) {

    // Normaliza token (remove espaços)
    $token = trim($token);

    // Validação básica do formato (aceita hex de tamanho razoável)
    if (!preg_match('/^[0-9a-fA-F]{6,256}$/', $token)) {
        $flash = ['type' => 'danger', 'message' => 'Token inválido. Verifique o link recebido por e-mail.'];
    } else {
        try {
            // Calcula hash do token (armazenamos apenas hash no DB)
            $hash = token_hash($token);

            // Detecta se a coluna token_type existe (compatibilidade com esquema recomendado)
            $colChk = $conn->prepare("
              SELECT 1
              FROM information_schema.columns
              WHERE table_schema = DATABASE()
                AND table_name = 'login_tokens'
                AND column_name = 'token_type'
              LIMIT 1
            ");
            $colChk->execute();
            $has_token_type = $colChk->fetchColumn() !== false;

            // Busca o token de CONFIRMAÇÃO referente a esse usuário.
            // Nota: se sua tabela armazena apenas tokens (sem token_type), usamos a mesma consulta.
            // Se você usa token_type, restringimos a token_type = 'confirmation' para segurança.
            if ($has_token_type) {
                $stmt = $conn->prepare("
                  SELECT id, psicologo_id, expires_at, used
                  FROM login_tokens
                  WHERE psicologo_id = :pid
                    AND token_hash = :h
                    AND token_type = 'confirmation'
                  LIMIT 1
                ");
            } else {
                $stmt = $conn->prepare("
                  SELECT id, psicologo_id, expires_at, used
                  FROM login_tokens
                  WHERE psicologo_id = :pid
                    AND token_hash = :h
                  LIMIT 1
                ");
            }

            $stmt->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
            $stmt->bindParam(':h', $hash);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                // Não encontrou o token — pode ser link errado, token já consumido ou DB inconsistente
                $flash = ['type' => 'danger', 'message' => 'Token inválido ou não encontrado. Solicite um novo link.'];
            } elseif ((int)$row['used'] === 1) {
                // Token já usado
                $flash = ['type' => 'danger', 'message' => 'Este token já foi utilizado. Solicite um novo link.'];
            } elseif (strtotime($row['expires_at']) <= time()) {
                // Token expirado
                $flash = ['type' => 'danger', 'message' => 'Token expirado. Solicite um novo link.'];
            } else {
                // Token válido -> proceder: marcar como usado, criar sessão, criar token persistente e setar cookie

                // 1) buscar email do psicólogo (só para garantir e popular a sessão)
                $s = $conn->prepare("SELECT email FROM psicologo WHERE id = :id LIMIT 1");
                $s->bindParam(':id', $psicologo_id, PDO::PARAM_INT);
                $s->execute();
                $ps = $s->fetch(PDO::FETCH_ASSOC);
                $email = $ps['email'] ?? null;

                // 2) marcar token de confirmação como usado (defensivo)
                $u = $conn->prepare("UPDATE login_tokens SET used = 1 WHERE id = :id");
                $u->bindParam(':id', $row['id'], PDO::PARAM_INT);
                $u->execute();

                // 3) criar token persistente (valor que será gravado no cookie) e armazenar APENAS o hash no DB
                $persistent_token = generate_token(16); // gera 32 hex chars
                $persistent_hash  = token_hash($persistent_token);
                $persistent_expires_ts = time() + $PERSISTENT_TTL_SECONDS;
                $persistent_expires_at = date('Y-m-d H:i:s', $persistent_expires_ts);

                if ($has_token_type) {
                    // Inserir com token_type = 'persistent'
                    $ins = $conn->prepare("
                      INSERT INTO login_tokens (psicologo_id, token_hash, expires_at, token_type, used)
                      VALUES (:pid, :h, :e, 'persistent', 0)
                    ");
                    $ins->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
                    $ins->bindParam(':h', $persistent_hash);
                    $ins->bindParam(':e', $persistent_expires_at);
                    $ins->execute();
                } else {
                    // Tabela sem token_type: insere no esquema antigo (coluna used existe)
                    $ins = $conn->prepare("
                      INSERT INTO login_tokens (psicologo_id, token_hash, expires_at, used)
                      VALUES (:pid, :h, :e, 0)
                    ");
                    $ins->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
                    $ins->bindParam(':h', $persistent_hash);
                    $ins->bindParam(':e', $persistent_expires_at);
                    $ins->execute();
                }

                // 4) escreve cookie com o valor do token PERSISTENTE (NÃO o hash).
                // Usa opções seguras: httponly, secure (se HTTPS) e SameSite=Lax.
                $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                if (PHP_VERSION_ID >= 70300) {
                    // array de opções (PHP >= 7.3)
                    setcookie('login_token', $persistent_token, [
                        'expires' => $persistent_expires_ts,
                        'path' => '/',
                        'secure' => $secure,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                } else {
                    // fallback para PHP mais antigo
                    setcookie('login_token', $persistent_token, $persistent_expires_ts, '/', '', $secure, true);
                }

                // 5) cria sessão e flash de sucesso
                $_SESSION['login_admin'] = $email;
                $_SESSION['psicologo_id'] = $psicologo_id;
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Login confirmado. Bem-vindo!'];

                // 6) redireciona para a página principal
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            // Em caso de erro no DB, log para diagnóstico e mensagem genérica ao usuário
            error_log('verify_login error: ' . $e->getMessage());
            $flash = ['type' => 'danger', 'message' => 'Erro interno. Tente novamente mais tarde.'];
        }
    }
}

// Se chegou aqui, ou não havia token/usuário, ou houve erro/flash -> exibe o formulário (HTML abaixo).
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Verificação de Login</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap icons (opcional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- favicon -->
    <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">

    <style>
        :root {
            --accent: #DBA632;
            --bg: #f4f7f9;
            --panel: #fff;
            --muted: #6c757d;
            --text: #333;
        }

        body {
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            margin: 0;
            padding: 24px;
        }

        .card-auth {
            width: 100%;
            max-width: 540px;
            background: var(--panel);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(31, 41, 55, 0.06);
            padding: 26px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
        }

        .logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
        }

        .logo-wrap img {
            width: 160px;
            height: auto;
            object-fit: contain;
        }

        .auth-title {
            text-align: center;
            font-weight: 700;
            color: var(--accent);
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .auth-sub {
            text-align: center;
            color: var(--muted);
            margin-bottom: 14px;
            font-size: 0.98rem;
        }

        .info-box {
            background: #e6fbff;
            border-left: 4px solid #b6f0ff;
            padding: .75rem 1rem;
            border-radius: 6px;
            color: #0b5b63;
            margin-bottom: 1rem;
        }

        .alert {
            margin-bottom: 1rem;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .input-group-text {
            background: #fff;
            border-right: none;
            border-radius: .375rem 0 0 .375rem;
        }

        .btn-mn {
            --g1: #fff;
            --g2: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            z-index: 0;
            color: var(--g1);
            background: var(--accent);
            border: none;
            width: 100%;
            box-shadow: 0 6px 18px rgba(16, 24, 40, 0.04);
            transition: background .16s ease, transform .08s ease, box-shadow .18s ease;
            text-align: center;
        }

        .btn-mn:hover,
        .btn-mn:focus {
            background: #c6932b;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(16, 24, 40, 0.08);
            outline: none;
        }

        .btn-mn:active {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(16, 24, 40, 0.06);
        }

        .mn-resend {
            position: relative;
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: .95rem;
            transition: color .28s ease;
            display: inline-block;
            padding-bottom: 2px;
        }

        .mn-resend::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -2px;
            transform: translateX(-50%) scaleX(0);
            width: 100%;
            height: 2px;
            background: var(--accent);
            transition: transform .28s ease;
        }

        .mn-resend:hover {
            color: var(--accent);
        }

        .mn-resend:hover::after {
            transform: translateX(-50%) scaleX(1);
        }

        .mn-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 12px;
            align-items: center;
        }

        /* Título e subtítulo */
        .mn-card h5 {
            margin: 0 0 6px 0;
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--text);
        }

        .mn-sub {
            color: var(--muted);
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .center {
            text-align: center;
        }

        @media (max-width: 480px) {
            .card-auth {
                padding: 18px;
            }

            .logo-wrap img {
                width: 120px;
            }
        }
    </style>
</head>

<body>

    <div class="mn-card" role="main" aria-labelledby="verifTitle">

        <div class="card-auth">

            <div class="logo-wrap">
                <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
            </div>

            <div class="center">
                <h5 id="verifTitle" style="color: #DBA632;">Confirme seu código</h5>

                <br>

                <?php if (isset($_GET['sent'])): ?>
                    <div class="info-box">
                        Enviamos um e-mail com um link e um código. Verifique seu e-mail e clique no link ou cole o código abaixo.
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <form method="POST" class="mt-3" novalidate>
                <input type="hidden" name="u" value="<?= htmlspecialchars($psicologo_id) ?>">

                <br>

                <div class="mb-3">
                    <label for="token" class="form-label fw-semibold">Código do e-mail</label>

                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            name="token"
                            id="token"
                            placeholder="Cole o token aqui (ex: 32 caracteres hex) ou cole o link recebido"
                            maxlength="256"
                            autocomplete="off"
                            required>
                    </div>
                    <div class="form-text">O token expira à meia-noite.</div>
                </div>

                <br>

                <div class="mn-actions">
                    <!-- Botão principal com estilo/uniformidade -->
                    <button type="submit" class="btn-mn">Confirmar</button>

                    <!-- Link de voltar (mesmo estilo/anim) -->
                    <a href="index.php" class="mn-resend">Voltar ao login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Se o usuário colar o link completo (com ?t=...), extrai automaticamente o token t= e coloca no campo.
        (function() {
            const input = document.getElementById('token');

            // quando colar ou digitar, detecta links
            input.addEventListener('input', tryExtractToken);

            // também ao carregar, se existe token na query string (ex: via ?t=...), já preenche
            (function fillFromQS() {
                const urlParams = new URLSearchParams(window.location.search);
                const t = urlParams.get('t');
                if (t) input.value = t;
            })();

            function tryExtractToken() {
                const val = input.value.trim();
                if (!val) return;

                // procura padrão t= na string (pode ser link completo ou apenas token)
                const match = val.match(/[?&]t=([0-9a-fA-F]+)/);
                if (match && match[1]) {
                    input.value = match[1];
                    return;
                }

                // alternativa: token no final (/t/abcdef)
                const match2 = val.match(/t=([0-9a-fA-F]+)/);
                if (match2 && match2[1]) {
                    input.value = match2[1];
                    return;
                }

                // se colaram uma URL com token no final após 't='
                try {
                    const url = new URL(val);
                    const tparam = url.searchParams.get('t');
                    if (tparam) input.value = tparam;
                } catch (e) {
                    // não é uma URL - nada a fazer
                }
            }
        })();
    </script>

</body>

</html>
