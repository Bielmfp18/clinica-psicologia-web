<?php
// send_reset.php
session_name('Mente_Renovada');
session_start();

require 'conn/conexao.php'; // sua conexão (pode ser $pdo ou $conn, este arquivo apenas exibe o form)
date_default_timezone_set('America/Sao_Paulo');

// Recupera flashes se houver (compatível com implementações anteriores)
$status_ok = false;
if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
    // se você usa 'status' dentro de flash, pega aqui
    if (!empty($_SESSION['flash']['status']) && $_SESSION['flash']['status'] === 'ok') {
        $status_ok = true;
    }
    // você pode ajustar conforme seu padrão de flash; aqui só mostramos a lógica básica
    unset($_SESSION['flash']);
}

// Também aceita ?status=ok na query (compatibilidade)
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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --accent: #DBA632;
            --bg: #f8f9fa;
            --card-radius: 12px;
            --card-shadow: 0 6px 18px rgba(16, 24, 40, 0.06);
            --muted: #6c757d;
            --text: #333;
            --mn-font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial,
                "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji",
                "Segoe UI Emoji", "Segoe UI Symbol";
        }

        /* Reset / font-smoothing */
        html,
        body {
            font-family: var(--mn-font-family);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Body geral */
        body {
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
        }

        /* Wrapper central que ocupa a altura da viewport */
        .forgot-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        /* Card principal */
        .forgot-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 32px;
            max-width: 520px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        /* Logo */
        .mn-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 8px;
        }

        .mn-logo img {
            height: 92px;
            object-fit: contain;
        }

        /* Título */
        h3.title {
            margin: 0;
            text-align: center;
            color: var(--accent);
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 1.5rem;
        }

        /* Texto explicativo */
        p.lead {
            text-align: center;
            color: var(--muted);
            margin-bottom: 18px;
            line-height: 1.45;
        }

        /* Label dos inputs */
        label.form-label {
            font-weight: 600;
            color: var(--text);
        }

        /* Botão principal (Enviar link) */
        .btn-custom {
            background-color: var(--accent);
            color: #fff;
            border: none;
            padding: 11px 14px;
            border-radius: 8px;
            font-weight: 700;
            width: 100%;
            transition: background-color .16s ease, transform .08s ease;
        }

        .btn-custom:hover {
            background-color: #c6932b;
            transform: translateY(-1px);
        }

        /* Link voltar ao login com linha animada */
        .mn-resend {
            display: inline-block;
            position: relative;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color .28s ease, transform .12s ease;
            padding-bottom: 4px;
            /* espaço para a linha animada */
        }

        .mn-resend::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%) scaleX(0);
            transform-origin: center;
            width: 100%;
            height: 2px;
            background: var(--accent);
            transition: transform .28s cubic-bezier(.2, .8, .2, 1), opacity .28s ease;
            opacity: 0.98;
        }

        .mn-resend:hover {
            color: var(--accent);
        }

        .mn-resend:hover::after {
            transform: translateX(-50%) scaleX(1);
        }

        .mn-resend:active {
            transform: scale(.98);
        }

        /* Input com ícone (group) */
        .input-with-icon {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .input-with-icon .input-group-text {
            background: #fff;
            border-right: 0;
        }

        .input-with-icon .form-control {
            border-left: 0;
        }

        /* Alert/Flash (usa classes do Bootstrap para comportamento, estilo leve aqui) */
        .forgot-card .alert {
            margin-top: 10px;
        }

        /* Responsividade */
        @media (max-width: 576px) {
            .forgot-card {
                padding: 20px;
                border-radius: 10px;
            }

            .mn-logo img {
                height: 74px;
            }

            h3.title {
                font-size: 1.3rem;
            }

            .btn-custom {
                padding: 10px;
            }
        }

        @media (max-width: 360px) {
            .forgot-card {
                padding: 16px;
            }

            .mn-logo img {
                height: 64px;
            }

            .mn-resend {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="forgot-wrapper">
        <div class="forgot-card">

            <div class="mn-logo">
                <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
            </div>

            <h3 class="title">Redefina a sua senha</h3>

            <?php if ($status_ok): ?>
                <div class="alert alert-success" role="alert">
                    Se o e-mail informado estiver cadastrado, um link de recuperação foi enviado. Verifique sua caixa de entrada e spam.
                </div>
            <?php else: ?>
                <p class="lead">
                    Insira o seu email e verifique sua caixa de entrada para mais instruções.
                    <br>Verifique também sua caixa de spam.
                </p>

                <form action="send_reset.php" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="forgot_email" class="form-label">Seu email</label>
                        <div class="input-group input-with-icon">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" id="forgot_email" class="form-control" placeholder="seu@exemplo.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-custom">Enviar link</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="index.php" class="mn-resend">Voltar ao login</a>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS (bundle inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>