<?php
// FORGOT PASSWORD 
session_name('Mente_Renovada');
session_start();

require 'conn/conexao.php'; // sua conexão (pode ser $pdo ou $conn, este arquivo apenas exibe o form)
date_default_timezone_set('America/Sao_Paulo');

// Recupera flashes se houver (compatível com implementações anteriores)
$status_ok = false;
if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    if (!empty($_SESSION['flash']['status']) && $_SESSION['flash']['status'] === 'ok') {
        $status_ok = true;
    }
    unset($_SESSION['flash']);
}

if (isset($_GET['status']) && $_GET['status'] === 'ok') {
    $status_ok = true;
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar senha</title>
    <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS + icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --accent: #DBA632;
            --bg: #f4f7f9;
            /* mesmo bg da página de verificação */
            --panel: #fff;
            --muted: #6c757d;
            --text: #333;
            --card-radius: 12px;
            --card-shadow: 0 10px 30px rgba(31, 41, 55, 0.06);
            --mn-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            font-family: var(--mn-font-family);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: var(--bg);
            color: var(--text);
        }

        .forgot-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            box-sizing: border-box;
        }

        .mn-card {
            width: 100%;
            max-width: 540px;
            background: var(--panel);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
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

        /* título no mesmo estilo da página de verificação */
        .auth-title {
            text-align: center;
            font-weight: 700;
            color: var(--accent);
            font-size: 1.15rem;
            margin-bottom: 4px;
        }

        .mn-sub {
            text-align: center;
            color: var(--muted);
            margin-bottom: 12px;
            font-size: 0.95rem;
            line-height: 1.45;
        }

        .info-box {
            background: #e6fbff;
            border-left: 4px solid #b6f0ff;
            padding: .75rem 1rem;
            border-radius: 6px;
            color: #0b5b63;
            margin-bottom: 1rem;
        }

        .flash-success {
            background: #e6f3ea;
            color: #08602b;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 14px;
            border: 1px solid rgba(11, 107, 58, 0.08);
            font-size: 0.95rem;
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
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

        @media (max-width: 480px) {
            .mn-card {
                padding: 18px;
            }

            .logo-wrap img {
                width: 120px;
            }

            .auth-title {
                font-size: 1.05rem;
            }
        }
    </style>
</head>

<body>
    <div class="forgot-wrapper">
        <main class="mn-card" role="main" aria-labelledby="forgotTitle">

            <div class="logo-wrap">
                <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
            </div>

            <h5 id="forgotTitle" class="auth-title">Redefina a sua senha</h5>

            <br>

            <?php if ($status_ok): ?>
                <div class="info-box">
                    Se o e-mail informado estiver cadastrado, um link de recuperação foi enviado. Verifique sua caixa de entrada e spam.
                </div>
            <?php else: ?>
                <div class="info-box text-center">
                    Insira o seu email e verifique sua caixa de entrada e spam para mais instruções.
                </div>

                <br>

                <form action="send_reset.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="forgot_email" class="form-label fw-semibold">Seu email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" id="forgot_email" class="form-control" placeholder="seu@exemplo.com" required>
                        </div>
                    </div>

                    <br>

                    <button type="submit" class="btn-mn">Enviar link</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="index.php" class="mn-resend">Voltar ao login</a>
            </div>

        </main>
    </div>

    <!-- Bootstrap JS (bundle inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>