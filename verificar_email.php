<?php
// VERIFICAR EMAIL

session_name('Mente_Renovada');
session_start();

$email = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verificar E-mail</title>

    <!-- Bootstrap (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- favicon -->
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

        body.mn-bg {
            display: flex;
            align-items: center;
            /* centra vertical */
            justify-content: center;
            /* centra horizontal */
            min-height: 100vh;
            /* garante altura cheia da janela */
            padding: 20px;
            /* espaço nas laterais em telas pequenas */
            background-color: var(--bg);
            color: var(--text);
            font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            margin: 0;
        }

        /* ajuste do card: remova margin-top fixo */
        .mn-card {
            width: calc(100% - 40px);
            max-width: 440px;
            margin: auto;
            background: var(--panel);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 22px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
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

        /* FLASH: classes por tipo */
        .flash-box {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 0.95rem;
            border: 1px solid transparent;
            box-sizing: border-box;
        }

        .flash-success {
            background: #e6f3ea;
            color: #08602b;
            border-color: rgba(11, 107, 58, 0.08);
        }

        .flash-danger {
            background: #fdecea;
            color: #8a130f;
            border-color: rgba(138, 19, 15, 0.08);
        }

        .flash-info {
            background: #e8f0ff;
            color: #10356b;
            border-color: rgba(16, 53, 107, 0.06);
        }

        .flash-warning {
            background: #fff7e6;
            color: #7a5200;
            border-color: rgba(219, 166, 50, 0.08);
        }

        /* Inputs e botões */
        .mn-input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 10px;
            border: 1px solid #e6e9ee;
            background: linear-gradient(180deg, #fff, #fbfdff);
            font-size: 0.95rem;
            color: var(--text);
            outline: none;
            transition: all .14s;
            box-sizing: border-box;
        }

        .mn-input:focus {
            border-color: rgba(219, 166, 50, 0.9);
            box-shadow: 0 6px 20px rgba(219, 166, 50, 0.08);
        }

        .mn-small {
            font-size: 0.88rem;
            color: var(--muted);
            display: block;
            margin-bottom: 6px;
        }

        .mn-resend {
            position: relative;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            transition: color .3s ease;
        }

        .mn-resend::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -2px;
            transform: translateX(-50%) scaleX(0);
            transform-origin: center;
            width: 100%;
            height: 2px;
            background: var(--accent);
            transition: transform .3s ease;
        }

        .mn-resend:hover {
            color: var(--accent);
        }

        .mn-resend:hover::after {
            transform: translateX(-50%) scaleX(1);
        }

        .mn-resend:active {
            transform: scale(0.95);
            transition: transform 120ms ease;
        }

        .btn-mn {
            --g1: #fff;
            --g2: #fff;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 999px;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            z-index: 0;
            color: var(--g1);
            background: linear-gradient(#DBA632, #DBA632) padding-box,
                /* interior branco */
                linear-gradient(90deg, var(--g1), var(--g2)) border-box;
            /* borda degradê */
            border: 2px solid transparent;
            /* a borda vem do border-box do background */
            box-shadow: 0 4px 12px rgba(219, 166, 50, 0.08);
            transition: color .18s ease, transform .12s ease, box-shadow .18s ease, background .18s ease;
            -webkit-font-smoothing: antialiased;
        }

        /* ícone dentro do botão (SVG) */
        .btn-mn .btn-icon {
            width: 18px;
            height: 18px;
            display: inline-block;
            flex: 0 0 18px;
            fill: currentColor;
            /* herda a cor do texto */
            opacity: 0.95;
        }

        /* hover: preenche com gradiente e deixa o texto branco */
        .btn-mn:hover,
        .btn-mn:focus {
            color: #DBA632;
            background: linear-gradient(90deg, var(--g1), var(--g2));
            /* preenchimento degradê */
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(219, 166, 50, .5);
            outline: none;
            border-color: #DBA632;
        }

        /* versão ativa/pressed */
        .btn-mn:active {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(219, 166, 50, 0.12);
        }


        /* versão ativa/pressed */
        .btn-mn:active {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(219, 166, 50, 0.12);
        }

        .mn-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 12px;
        }

        @media (max-width:480px) {
            .mn-card {
                margin: 18px;
                padding: 16px;
                border-radius: 12px;
            }

            .mn-logo img {
                height: 48px;
            }

            .mn-actions {
                flex-direction: column-reverse;
                align-items: stretch;
                gap: 10px;
            }

            .btn-mn {
                width: 100%;
                justify-content: center;
                padding: 12px;
                border-radius: 10px;
            }
        }

        .mn-card .flash-box {
            font-weight: 600;
        }

        .mn-flash {
            display: none !important;
        }

        @media (max-width: 480px) {

            /* empilha os elementos e centraliza */
            .mn-actions {
                flex-direction: column-reverse;
                /* mantém o botão por cima se já quiser assim */
                align-items: center;
                /* centraliza itens no eixo horizontal */
                gap: 10px;
            }

            /* faz o link ocupar toda a linha e centraliza o texto */
            .mn-resend {
                display: block;
                width: 100%;
                text-align: center;
                font-weight: 600;
            }

            /* botão preenchido ocupa largura total (opcional, já tinha) */
            .btn-mn {
                width: 100%;
                padding: 12px;
                border-radius: 10px;
            }
        }
    </style>
</head>

<body class="mn-bg">
    <div class="mn-card" role="main" aria-labelledby="verifTitle">
        <div class="mn-logo">
            <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
        </div>

        <div class="center">
            <h5 id="verifTitle">Confirme seu e-mail</h5>
            <p class="mn-sub">Enviamos um código de 6 dígitos para: <strong><?= htmlspecialchars($email) ?></strong></p>
        </div>

        <br>

        <!-- Renderiza flash com base no tipo -->
        <?php if (!empty($flash)):
            $type = $flash['type'] ?? 'info';
            $map = ['success' => 'flash-success', 'danger' => 'flash-danger', 'info' => 'flash-info', 'warning' => 'flash-warning'];
            $cls = $map[$type] ?? 'flash-info';
        ?>
            <div class="flash-box <?= $cls ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <form action="verificar_handler.php" method="POST" novalidate>
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <div class="mb-2">
                <label for="codigo" class="mn-small">Código de verificação</label>
                <input id="codigo" name="codigo" class="form-control mn-input" maxlength="6" pattern="\d{6}" placeholder="Ex.: 123456" required>
            </div>

            <div class="mn-actions">
                <a class="mn-resend" id="resend-link" href="reenviar_codigo.php?email=<?= urlencode($email) ?>">Reenviar código</a>
                <button type="submit" class="btn-mn">Confirmar</button>
            </div>
        </form>
    </div>

    <script>
        // animação visual curta no clique do link "Reenviar código"
        document.addEventListener('DOMContentLoaded', function() {
            const resend = document.getElementById('resend-link');
            if (!resend) return;
            resend.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                this.style.transition = 'transform 120ms ease';
                setTimeout(() => this.style.transform = '', 120);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>