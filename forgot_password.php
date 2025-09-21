<?php
// send_reset.php
// Trata GET (exibe formulário) e POST (envia link de reset).
session_start();
require 'conn/conexao.php'; // deve prover $pdo (PDO) idealmente

date_default_timezone_set('America/Sao_Paulo');

/*
  CONFIG - ajuste conforme seu ambiente
  -> Em produção coloque o PEPPER em variável de ambiente e não aqui no código.
*/
$PEPPER = getenv('RESET_PEPPER') ?: 'troque_esta_pepper_por_uma_mUITO_secreta_string'; // MUDE para algo secreto
$RESET_EXPIRES_SECONDS = 60 * 60; // 1 hora
$MAX_REQUESTS_PER_HOUR = 5;
$FROM_EMAIL = 'no-reply@seudominio.com';
$FROM_NAME  = 'Mente Renovada';

// Detecta PDO
$db = $pdo ?? null;
if (!$db) {
    // tenta detectar $conn que pode ser um PDO com outro nome
    $db = $conn ?? null;
}

// Se não houver objeto PDO, log e continue (form ainda será exibido, mas POST falhará com mensagem genérica).
if (!$db || !($db instanceof PDO)) {
    error_log("send_reset.php: conexão PDO não encontrada em conn/conexao.php. Verifique se \$pdo está definido.");
}

// Função auxiliar para mensagens flash
function flash_set($key, $message)
{
    $_SESSION['flash'][$key] = $message;
}
function flash_get_all()
{
    $r = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $r;
}

// Se for POST, processa o envio do link
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // se não temos PDO, retornamos mensagem genérica
    if (!$db || !($db instanceof PDO)) {
        // não expor detalhes ao usuário
        flash_set('status', 'ok');
        header('Location: send_reset.php');
        exit;
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        // Mensagem genérica (não confirma se o email existe)
        flash_set('status', 'ok');
        header('Location: send_reset.php');
        exit;
    }

    try {
        // 1) Busca psicólogo pelo email (não diferencia maiúsc/minúsc dependendo do collation do DB)
        $stmt = $db->prepare('SELECT id, email FROM psicologo WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Resposta genérica ao usuário (evita enumeração)
        $redirectLocation = 'Location: send_reset.php?status=ok';

        if (!$user) {
            // Mesmo que não exista, mostramos sucesso (não confirmar existência)
            flash_set('status', 'ok');
            header($redirectLocation);
            exit;
        }

        $uid = (int)$user['id'];

        // 2) Checar limite de requests na última hora
        $stmt = $db->prepare('SELECT COUNT(*) FROM password_resets WHERE psicologo_id = :uid AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)');
        $stmt->execute([':uid' => $uid]);
        $count = (int)$stmt->fetchColumn();

        if ($count >= $MAX_REQUESTS_PER_HOUR) {
            // log e resposta genérica
            error_log("send_reset: bloqueado por rate limit para usuario $uid");
            flash_set('status', 'ok');
            header($redirectLocation);
            exit;
        }

        // 3) gerar token seguro e armazenar apenas o hash
        $token = bin2hex(random_bytes(32)); // 64 hex chars
        $token_hash = hash('sha256', $token . $PEPPER);
        $expires_at = date('Y-m-d H:i:s', time() + $RESET_EXPIRES_SECONDS);

        $insert = $db->prepare('INSERT INTO password_resets (psicologo_id, token_hash, expires_at) VALUES (:uid, :token_hash, :expires_at)');
        $insert->execute([
            ':uid' => $uid,
            ':token_hash' => $token_hash,
            ':expires_at' => $expires_at
        ]);

        // 4) montar link de reset com segurança (sanitize host)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        // sanitize host para evitar header injection
        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
        $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $reset_link = sprintf('%s://%s%s/reset_password.php?uid=%d&token=%s', $scheme, $host, $path, $uid, $token);

        // 5) montar e enviar e-mail (em produção, substitua por PHPMailer/SMTP)
        $subject = 'Redefinição de senha - Mente Renovada';
        $message_html = "
            <p>Olá,</p>
            <p>Recebemos uma solicitação para redefinir a sua senha. Clique no link abaixo para criar uma nova senha. Esse link expira em 1 hora.</p>
            <p><a href=\"$reset_link\">Redefinir senha</a></p>
            <p>Se você não solicitou essa alteração, ignore este e-mail.</p>
            <hr>
            <p>Mente Renovada</p>
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$FROM_NAME} <{$FROM_EMAIL}>\r\n";

        // Tente enviar (pode falhar em host sem config SMTP)
        $sent = @mail($user['email'], $subject, $message_html, $headers);

        if (!$sent) {
            // log para debug; mas não informar o usuário
            error_log("send_reset: falha ao enviar email para {$user['email']}");
            // Ainda assim retornamos sucesso para o usuário para evitar enumeração.
        }

        // Mensagem ao usuário (genérica)
        flash_set('status', 'ok');
        header($redirectLocation);
        exit;
    } catch (Exception $e) {
        error_log("send_reset exception: " . $e->getMessage());
        // Mensagem genérica
        flash_set('status', 'ok');
        header('Location: send_reset.php');
        exit;
    }
}

// Se chegou aqui, é GET (ou falha): exibe o formulário.
// Recupera flashes se houver
$flashes = flash_get_all();
$status_ok = ($flashes['status'] ?? $_GET['status'] ?? null) === 'ok';
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar senha</title>
    <!-- Link para o ícone da aba -->
    <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .forgot-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            max-width: 480px;
            width: 100%;
        }

        .btn-custom {
            background-color: #DBA632;
            color: #fff;
        }

        .btn-custom:hover {
            background-color: #c28f2c;
            color: #fff;
        }

        .mn-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
        }

        .mn-logo img {
            height: 94px;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <div class="forgot-wrapper">
        <div class="forgot-card">

            <div class="mn-logo">
                <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
            </div>

            <h3 class="text-center mb-3" style="color:#DBA632;">Redefina a sua senha</h3>

            <?php if ($status_ok): ?>
                <div class="alert alert-success" role="alert">
                    Se o e-mail informado estiver cadastrado, um link de recuperação foi enviado. Verifique sua caixa de entrada e spam.
                </div>
            <?php else: ?>
                <p class="text-center text-muted mb-4">
                    Insira o seu email e verifique sua caixa de entrada para mais instruções.
                    <br>Verifique também sua caixa de spam.
                </p>

                <br>
                
                <form action="send_reset.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="forgot_email" class="form-label">Seu email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" id="forgot_email" class="form-control" placeholder="seu@exemplo.com" required>
                        </div>
                    </div>

                    <!-- Se quiser adicionar reCAPTCHA aqui depois -->

                    <button type="submit" class="btn btn-custom w-100">Enviar link</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="index.php" style="color:#333;">Voltar ao login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>