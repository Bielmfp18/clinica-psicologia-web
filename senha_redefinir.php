<?php
// SENHA REDEFINIR

session_name('Mente_Renovada');
session_start();
include "conn/conexao.php";

$token = $_GET['token'] ?? '';
if (!$token || !preg_match('/^[0-9a-f]{64}$/', $token)) {
    die('Link inválido.');
}

$token_hash = hash('sha256', $token);
$stmt = $conn->prepare("SELECT pr.id AS reset_id, pr.psicologo_id, pr.expires_at, pr.used, p.email 
                        FROM password_resets pr 
                        JOIN psicologo p ON p.id = pr.psicologo_id 
                        WHERE pr.token_hash = :th LIMIT 1");
$stmt->bindParam(':th', $token_hash);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Token inválido ou já utilizado.');
}

if ((int)$row['used'] === 1) {
    die('Este link já foi utilizado.');
}

$expires = new DateTime($row['expires_at']);
$now = new DateTime();
if ($now > $expires) {
    die('Token expirado. Solicite novamente a redefinição de senha.');
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
        <p class="mn-sub">Conta: <strong><?= htmlspecialchars($row['email']) ?></strong></p>

        <br>

        <form action="reset_handler.php" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label for="senha">Nova senha</label>
            <input id="senha" type="password" name="senha" class="mn-input" required minlength="6">

            <label for="senha_confirm">Confirmar senha</label>
            <input id="senha_confirm" type="password" name="senha_confirm" class="mn-input" required minlength="6">

            <button type="submit" class="btn-mn">Salvar nova senha</button>
        </form>
    </div>
</body>

</html>