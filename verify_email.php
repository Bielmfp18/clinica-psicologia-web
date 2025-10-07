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

        /* título dourado centralizado — reduzido conforme pedido */
        .auth-title {
            text-align: center;
            font-weight: 700;
            color: var(--accent);
            font-size: 1.15rem;
            /* reduzido */
            margin-bottom: 6px;
        }

        /* subtítulo/mensagem em caixa azul (igual à primeira imagem) */
        .info-box {
            background: #e6fbff;
            border-left: 4px solid #b6f0ff;
            padding: .85rem 1rem;
            border-radius: 6px;
            color: #0b5b63;
            margin-bottom: 1rem;
            font-size: 0.98rem;
            line-height: 1.45;
            text-align: center;
        }

        .mn-sub {
            text-align: center;
            color: var(--muted);
            margin-bottom: 12px;
            font-size: 0.95rem;
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

        .form-text {
            color: var(--muted);
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

        .input-group.invalid .input-group-text {
            background: #fff5f5;
            color: #a94442;
            border-color: #dc3545;
        }

        .input-group.invalid .form-control,
        .form-control.input-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 .12rem rgba(220, 53, 69, 0.12);
        }

        /* mensagem de erro */
        .mn-code-error {
            color: #a94442;
            font-size: 0.9rem;
            margin-top: 6px;
            display: none;
        }

        /* ativa */
        .mn-code-error.active {
            display: block;
        }

        /* animação sutil para erro */
        @keyframes shake {
            0% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-6px);
            }

            50% {
                transform: translateX(6px);
            }

            75% {
                transform: translateX(-4px);
            }

            100% {
                transform: translateX(0);
            }
        }

        .input-group.invalid.shake {
            animation: shake .36s ease-in-out;
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

            /* reduzido no mobile também */
        }
    </style>
</head>

<body>
    <div class="wrap">
        <main class="mn-card" role="main" aria-labelledby="verifTitle">

            <div class="logo-wrap">
                <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
            </div>


            <h5 id="verifTitle" class="auth-title">Confirme seu e-mail</h5>


            <!-- <div class="info-box" role="status" aria-live="polite">
                Enviamos um e-mail com um link e um código. Verifique seu e-mail e clique no link ou cole o código abaixo.
            </div> -->

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
                    <a href="index.php" class="mn-resend">Voltar ao login</a>
                </div>
            </form>

        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // clique visual no reenviar
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

    <script>
(function(){
  const form = document.querySelector('form[action="verificar_handler.php"], form[action="./verificar_handler.php"]') || document.querySelector('form');
  const codeInput = document.getElementById('codigo');

  if (!form || !codeInput) return;

  // Cria/garante elemento de mensagem de erro
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

  // Validação: precisa ter 6 dígitos numéricos
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

  // Ao digitar
  codeInput.addEventListener('input', function() {
    const val = codeInput.value;
    // Se vazio, limpa erro
    if (val.trim() === '') {
      setValidityState(true);
      return;
    }
    setValidityState(isValidCode(val));
  });

  // Ao sair do campo
  codeInput.addEventListener('blur', function() {
    const ok = isValidCode(codeInput.value);
    setValidityState(ok);
  });

  // Ao enviar o formulário
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
})();
</script>


</body>

</html>