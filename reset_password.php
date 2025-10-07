<?php
// reset_password.php
require_once __DIR__ . '/conn/conexao.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('Mente_Renovada');
    session_start();
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Se vier por GET, apenas exibe o formulário; a verificação principal acontece no reset_handler.php.
// Mas podemos checar sintaxe básica do token/uid.
$token = $_GET['t'] ?? '';
$uid = $_GET['u'] ?? '';

/*
  Abaixo tentamos buscar o e-mail associado ao token para mostrar "Conta: xxx".
  Se o token for inválido, usado ou expirado, marcamos $token_valid = false
  e não mostramos o formulário de alteração de senha.
*/
$token_valid = false;
$accountEmail = null;

if (!empty($token) && !empty($uid)) {
    try {
        $token_h = token_hash($token);
        $psicologo_id = (int)$uid;

        $sel = $conn->prepare("
            SELECT pr.id AS reset_id, pr.expires_at, pr.used, p.email
            FROM password_resets pr
            JOIN psicologo p ON p.id = pr.psicologo_id
            WHERE pr.token_hash = :h
              AND pr.psicologo_id = :pid
            LIMIT 1
        ");
        $sel->bindParam(':h', $token_h);
        $sel->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
        $sel->execute();
        $row = $sel->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // checa se já usado
            if ((int)$row['used'] === 0 && strtotime($row['expires_at']) >= time()) {
                $token_valid = true;
                $accountEmail = $row['email'];
            } else {
                $token_valid = false;
            }
        } else {
            $token_valid = false;
        }
    } catch (Exception $e) {
        error_log("reset_password lookup error: " . $e->getMessage());
        $token_valid = false;
    }
} else {
    $token_valid = false;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Nova senha</title>
  <title>Redefinir senha</title>
    <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">
    <style>
        :root {
            --accent: #DBA632;
            --bg: #f3f6f9;
            --panel: #ffffff;
            --muted: #9aa3ad;
            --text: #333;
            --card-radius: 14px;
            --card-shadow: 0 10px 30px rgba(16, 24, 40, 0.06);
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background-color: var(--bg);
            font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            color: var(--text);
        }

        .mn-card {
            width: calc(100% - 40px);
            max-width: 440px;
            background: var(--panel);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 24px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
        }

        .mn-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }

        .mn-logo img {
            height: 90px;
            object-fit: contain;
        }

        h4 {
            margin: 0 0 8px;
            font-weight: 700;
            font-size: 1.2rem;
            text-align: center;
            color: var(--text);
        }

        p.mn-sub {
            text-align: center;
            color: var(--muted);
            margin-bottom: 16px;
            font-size: 0.95rem;
        }

        label {
            display: block;
            font-size: 0.88rem;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .mn-input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 10px;
            border: 1px solid #e6e9ee;
            background: linear-gradient(180deg, #fff, #fbfdff);
            font-size: 0.95rem;
            color: var(--text);
            margin-bottom: 14px;
            transition: all .14s;
            box-sizing: border-box;
        }

        .mn-input:focus {
            border-color: rgba(219, 166, 50, 0.9);
            box-shadow: 0 6px 20px rgba(219, 166, 50, 0.08);
            outline: none;
        }

        .btn-mn {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            color: #fff;
            background: var(--accent);
            box-shadow: 0 4px 12px rgba(219, 166, 50, 0.18);
            transition: all .2s ease;
        }

        .btn-mn:hover {
            background: #c6932b;
            transform: scale(1.02);
        }

        .msg-invalid {
            text-align: center;
            color: #a94442;
            background: #f2dede;
            border: 1px solid #ebcccc;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 14px;
        }

        @media (max-width:480px) {
            .mn-card {
                margin: 18px;
                padding: 18px;
            }

            .mn-logo img {
                height: 60px;
            }
        }
    </style>
</head>

<body>
    <div class="mn-card">
        <div class="mn-logo">
            <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
        </div>
        <h4>Redefinir senha</h4>

        <?php if ($token_valid): ?>
            <p class="mn-sub">Conta: <strong><?= htmlspecialchars($accountEmail) ?></strong></p>

            <br>

            <form action="reset_handler.php" method="POST">
                <!-- use nomes compatíveis com reset_handler.php (t e u) -->
                <input type="hidden" name="t" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="u" value="<?= htmlspecialchars($uid) ?>">

                <label for="senha">Nova senha</label>
                <input id="senha" type="password" name="senha" class="mn-input" required minlength="8" placeholder="Mínimo 8 caracteres">

                <label for="senha_confirm">Confirmar senha</label>
                <input id="senha_confirm" type="password" name="senha2" class="mn-input" required minlength="8">

                <button type="submit" class="btn-mn">Salvar nova senha</button>
            </form>
        <?php else: ?>
            <div class="msg-invalid">
                <strong>Link inválido ou expirado.</strong><br>
                Solicite novamente a recuperação de senha.
            </div>
            <div style="display:flex; gap:8px;">
                <a href="forgot_password.php" class="btn-mn" style="background:#6c757d; box-shadow:none; text-decoration:none; display:inline-block; text-align:center; padding:10px 12px; border-radius:8px; color:#fff; width:100%;">Solicitar novo link</a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
