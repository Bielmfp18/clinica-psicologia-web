<?php
// VERIFICAR EMAIL

session_name('Mente_Renovada');
session_start();

$email = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// cria url de reenviar (ajuste o nome do handler se necessário)
$resendHref = !empty($email) ? 'reenviar_codigo.php?email=' . urlencode($email) : 'index.php';
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verificar E-mail</title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS + icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- favicon -->
    <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">

    <style>
        :root {
            --accent: #DBA632;
            --bg: #f4f7f9;
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

        .wrap {
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
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 36px 26px 34px 26px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-sizing: border-box;
            position: relative;
        }

        .mn-resend {
            position: relative;
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: .95rem;
            transition: color .28s ease;
            display: inline-block;
            padding-bottom: 4px;
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

        .btn-anim {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-anim:hover {
            transform: scale(1.07);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-anim:active {
            transform: scale(0.97);
        }


        .logo-wrap {
            display: flex;
            justify-content: center;
            margin: 6px 0 8px 0;
        }

        .logo-wrap img {
            width: 120px;
            height: auto;
            object-fit: contain;
        }

        .auth-title {
            text-align: center;
            font-weight: 700;
            color: var(--accent);
            font-size: 1.15rem;
            margin-bottom: 6px;
        }

        .flash {
            margin-bottom: 12px;
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

        /* Reenviar centralizado — usamos botão outline para combinar com seu exemplo */
        .btn-resend-center {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 700;
            color: var(--accent);
            background: transparent;
            border: 1px solid rgba(13, 110, 253, 0.08);
            cursor: pointer;
            transition: background .12s ease, transform .08s ease, opacity .12s ease;
            margin-top: 18px;
        }

        .btn-resend-center i {
            font-size: 1.05rem;
        }

        .btn-resend-center:hover {
            background: rgba(13, 110, 253, 0.04);
            transform: translateY(-1px);
        }

        .btn-resend-center:disabled {
            opacity: .6;
            cursor: default;
            transform: none;
        }

        .status-msg {
            font-size: 0.95rem;
            color: var(--muted);
            margin-top: 12px;
            text-align: center;
            min-height: 20px;
        }

        .mn-code-error {
            color: #a94442;
            font-size: 0.9rem;
            margin-top: 6px;
            display: none;
        }

        .mn-code-error.active {
            display: block;
        }

        @keyframes shake {
            0% {
                transform: translateX(0)
            }

            25% {
                transform: translateX(-6px)
            }

            50% {
                transform: translateX(6px)
            }

            75% {
                transform: translateX(-4px)
            }

            100% {
                transform: translateX(0)
            }
        }

        .input-group.invalid.shake {
            animation: shake .36s ease-in-out;
        }

        @media (max-width:480px) {
            .logo-wrap img {
                width: 100px
            }

            .mn-card {
                padding: 28px 14px
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <main class="mn-card" role="main" aria-labelledby="verifTitle">

            <!-- botão voltar no canto superior esquerdo (igual ao seu exemplo) -->
            <a href="index.php"
                class="btn btn-outline-primary position-absolute top-0 start-0 m-3 btn-anim"
                title="Voltar ao login"
                id="back-btn">
                <i class="bi bi-arrow-left-short"></i>
            </a>

            <div class="logo-wrap">
                <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
            </div>

            <h5 id="verifTitle" class="auth-title">Confirme seu e-mail</h5>

            <br>

            <?php if (!empty($flash)):
                $type = $flash['type'] ?? 'info';
                $map = ['success' => 'alert-success', 'danger' => 'alert-danger', 'info' => 'alert-info', 'warning' => 'alert-warning'];
                $cls = $map[$type] ?? 'alert-info';
            ?>
                <div class="alert <?= $cls ?> flash" role="alert"><?= htmlspecialchars($flash['message']) ?></div>
            <?php endif; ?>

            <form action="verificar_handler.php" method="POST" class="mt-2" novalidate>
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <div class="mb-3">
                    <label for="codigo" class="form-label fw-semibold">Código de verificação</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                        <input id="codigo" name="codigo" type="text" class="form-control" maxlength="6" pattern="\d{6}" placeholder="Ex.: 123456" autocomplete="one-time-code" required>
                    </div>
                </div>

                <br>

                <div class="mn-actions">
                    <button type="submit" class="btn-mn">Confirmar</button>

                    <br>
                
                    <!-- Alteração mínima: adicionado id, data-href e span interno com id resend-text (mantendo classe mn-resend para não alterar estilo) -->
                    <a href="<?= htmlspecialchars($resendHref) ?>"
                        id="resend-btn"
                        class="mn-resend"
                        role="button"
                        aria-disabled="false"
                        data-href="<?= htmlspecialchars($resendHref) ?>">
                        <span id="resend-text">Reenviar Código</span>
                    </a>
                </div>
    </div>
    </form>
    </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // mostra erros JS na área de status (ajuda a debugar se algo quebrar)
        window.addEventListener('error', function(ev) {
            const status = document.getElementById('status-area');
            if (status) status.textContent = 'Erro JS: ' + (ev.message || 'ver console');
            console.error('Erro JS capturado:', ev.error || ev.message, ev);
        });
        window.addEventListener('unhandledrejection', function(ev) {
            const status = document.getElementById('status-area');
            if (status) status.textContent = 'Promise rejeitada: ver console';
            console.error('Promise rejeitada:', ev.reason);
        });
    </script>

    <script>
        (function() {
            try {
                const resendBtn = document.getElementById('resend-btn');
                const resendText = document.getElementById('resend-text');
                const statusArea = document.getElementById('status-area');

                if (!resendBtn) return;

                const COOLDOWN = 60; // segundos

                function startCooldown(seconds) {
                    let remaining = seconds;
                    // para elementos que não suportam disabled nativamente (ex: <a>), usamos aria-disabled + classe
                    try {
                        resendBtn.disabled = true;
                    } catch (e) {}
                    resendBtn.classList.add('disabled');
                    resendBtn.setAttribute('aria-disabled', 'true');
                    updateResendText(remaining);
                    const timer = setInterval(() => {
                        remaining -= 1;
                        if (remaining <= 0) {
                            clearInterval(timer);
                            resetResendButton();
                            if (statusArea) statusArea.textContent = '';
                            return;
                        }
                        updateResendText(remaining);
                    }, 1000);
                }

                function updateResendText(sec) {
                    if (resendText) {
                        resendText.textContent = `Reenviar (${sec}s)`;
                    } else {
                        resendBtn.textContent = `Reenviar (${sec}s)`;
                    }
                }

                function resetResendButton() {
                    try {
                        resendBtn.disabled = false;
                    } catch (e) {}
                    resendBtn.classList.remove('disabled');
                    resendBtn.setAttribute('aria-disabled', 'false');
                    if (resendText) resendText.textContent = 'Reenviar Código';
                }

                resendBtn.addEventListener('click', function(ev) {
                    try {
                        ev.preventDefault(); // prevenir comportamento padrão do link para controlar o fluxo
                        const href = this.dataset.href;
                        if (!href) {
                            if (statusArea) statusArea.textContent = 'Link de reenviar ausente.';
                            return;
                        }

                        // animação rápida de clique
                        this.style.transform = 'scale(0.98)';
                        this.style.transition = 'transform 120ms ease';
                        setTimeout(() => this.style.transform = '', 120);

                        if (statusArea) statusArea.textContent = 'Tentando reenviar o código...';
                        startCooldown(COOLDOWN);

                        // redireciona para o seu handler que fará o envio e setará o flash
                        setTimeout(() => {
                            window.location.href = href;
                        }, 250);
                    } catch (err) {
                        console.error('Erro no click do resendBtn:', err);
                        if (statusArea) statusArea.textContent = 'Erro ao reenviar (ver console).';
                    }
                });

                // opção: se você calcular remaining no servidor (verification_sent_at),
                // pode injetar um valor PHP e chamar startCooldown(serverRemaining) aqui.
            } catch (err) {
                console.error('Erro no script de reenviar:', err);
                const s = document.getElementById('status-area');
                if (s) s.textContent = 'Erro (reenviar): ' + err.message;
            }
        })();
    </script>

    <script>
        (function() {
            try {
                const form = document.querySelector('form[action="verificar_handler.php"], form[action="./verificar_handler.php"]') || document.querySelector('form');
                const codeInput = document.getElementById('codigo');
                if (!form || !codeInput) return;

                function ensureErrorElement() {
                    let err = codeInput.parentElement.parentElement.querySelector('.mn-code-error');
                    if (!err) {
                        err = document.createElement('div');
                        err.className = 'mn-code-error';
                        err.setAttribute('aria-live', 'polite');
                        err.textContent = 'Digite um código válido de 6 números.';
                        const wrapper = codeInput.closest('.input-group') || codeInput.parentElement;
                        wrapper.insertAdjacentElement('afterend', err);
                    }
                    return err;
                }

                function isValidCode(value) {
                    return /^\d{6}$/.test(value.trim());
                }

                function setValidityState(isValid) {
                    const inputGroup = codeInput.closest('.input-group');
                    const err = ensureErrorElement();
                    if (isValid) {
                        if (inputGroup) inputGroup.classList.remove('invalid', 'shake');
                        codeInput.classList.remove('input-invalid');
                        err.classList.remove('active');
                        codeInput.setAttribute('aria-invalid', 'false');
                    } else {
                        if (inputGroup) inputGroup.classList.add('invalid');
                        codeInput.classList.add('input-invalid');
                        err.classList.add('active');
                        codeInput.setAttribute('aria-invalid', 'true');
                    }
                }

                codeInput.addEventListener('input', function() {
                    const val = codeInput.value;
                    if (val.trim() === '') {
                        setValidityState(true);
                        return;
                    }
                    setValidityState(isValidCode(val));
                });

                codeInput.addEventListener('blur', function() {
                    setValidityState(isValidCode(codeInput.value));
                });

                form.addEventListener('submit', function(ev) {
                    const ok = isValidCode(codeInput.value);
                    if (!ok) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        const inputGroup = codeInput.closest('.input-group');
                        if (inputGroup) {
                            inputGroup.classList.add('shake');
                            setTimeout(() => inputGroup.classList.remove('shake'), 400);
                        }
                        setValidityState(false);
                        codeInput.focus();
                        return false;
                    }
                    return true;
                });
            } catch (err) {
                console.error('Erro no bloco de validação:', err);
                const s = document.getElementById('status-area');
                if (s) s.textContent = 'Erro (validação): ' + err.message;
            }
        })();
    </script>
</body>

</html>