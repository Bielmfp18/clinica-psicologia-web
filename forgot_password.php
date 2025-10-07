<?php
// FORGOT PASSWORD 
session_name('Mente_Renovada');
session_start();

require 'conn/conexao.php'; // sua conexão (pode ser $pdo ou $conn, este arquivo apenas exibe o form)
require_once __DIR__ . '/helpers.php';     // funcoes: generate_token, token_hash, days_from_now_datetime, esc, etc
require_once __DIR__ . '/send_email.php';  // wrapper com PHPMailer
date_default_timezone_set('America/Sao_Paulo');

// -------------------------------------------------
// LÓGICA: quando o formulário for enviado (POST) -> criar token e enviar e-mail
// Mantive seus comentários e estrutura; adicionei TRY/CATCH e PRG (redirect after POST).
// -------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    // validação básica
    if (empty($email)) {
        // Mantemos a mesma forma de flash que seu layout já espera
        $_SESSION['flash'] = ['status' => 'err', 'message' => 'Informe um e-mail válido.'];
        header('Location: forgot_password.php');
        exit;
    }

    try {
        // busca o psicólogo (não revelar se existe ou não)
        $sel = $conn->prepare("SELECT id, nome FROM psicologo WHERE email = :email LIMIT 1");
        $sel->bindParam(':email', $email);
        $sel->execute();
        $row = $sel->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $psid = (int)$row['id'];
            $nome = $row['nome'] ?? '';

            // gera token raw e hash (armazenar hash)
            $token = generate_token(16); // ex: 32 chars hex
            $hash  = token_hash($token);
            $expires_at = days_from_now_datetime(1); // 1 dia de validade

            // insere no password_resets (tabela criada no seu script SQL)
            $ins = $conn->prepare("INSERT INTO password_resets (psicologo_id, token_hash, expires_at, used, created_at) VALUES (:pid, :h, :e, 0, NOW())");
            $ins->bindParam(':pid', $psid, PDO::PARAM_INT);
            $ins->bindParam(':h', $hash);
            $ins->bindParam(':e', $expires_at);
            $ins->execute();

            // monta link robusto para reset_password.php
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            if ($basePath === '.' || $basePath === '/' || $basePath === '\\') {
                $basePath = '';
            }
            $resetLink = $scheme . '://' . $host . $basePath . '/reset_password.php?t=' . urlencode($token) . '&u=' . urlencode($psid);

            // corpo do e-mail (HTML e plain)
            $subject = 'Redefinição de senha - Mente Renovada';
            $html = "<p>Olá " . esc($nome) . ",</p>"
                . "<p>Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo para escolher uma nova senha (válido 24 horas):</p>"
                . "<p><a href=\"" . esc($resetLink) . "\">Redefinir minha senha</a></p>"
                . "<p>Se preferir, copie/cole este código: <strong>" . esc($token) . "</strong></p>"
                . "<p>Se você não solicitou, ignore este e-mail.</p>";

            $plain = "Olá {$nome},\n\n"
                . "Recebemos uma solicitação para redefinir sua senha. Acesse o link abaixo (válido 24h):\n"
                . "{$resetLink}\n\n"
                . "Código: {$token}\n\n"
                . "Se você não solicitou, ignore.";

            // envia e-mail (wrapper PHPMailer)
            $sent = send_verification_email($email, $nome, $subject, $html, $plain);

            // mesmo se o envio falhar, por segurança não revelamos ao usuário se conta existe.
            // Indicamos sucesso para o fluxo (mensagem genérica).
            $_SESSION['flash'] = ['status' => 'ok'];
            header('Location: forgot_password.php?status=ok');
            exit;
        } else {
            // não encontrou conta -> não revelamos; comporta-se igualmente
            $_SESSION['flash'] = ['status' => 'ok'];
            header('Location: forgot_password.php?status=ok');
            exit;
        }
    } catch (Exception $e) {
        // registra erro para diagnostico e retorna mensagem genérica
        error_log("forgot_password error: " . $e->getMessage());
        $_SESSION['flash'] = ['status' => 'ok']; // evitar vazar info; informe o usuário que enviamos se existir
        header('Location: forgot_password.php?status=ok');
        exit;
    }
}

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
        .mn-email-error {
            color: #a94442;
            font-size: 0.9rem;
            margin-top: 6px;
            display: none;
        }

        /* quando ativa */
        .mn-email-error.active {
            display: block;
        }

        /* animação sutil para chamar atenção no submit inválido */
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

                <form action="forgot_password.php" method="POST" novalidate>
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

    <script>
(function(){
  // seletores (ajuste caso seu id seja diferente)
  const form = document.querySelector('form[action="forgot_password.php"], form[action="./forgot_password.php"], form[action="forgot_password.php"]') || document.querySelector('form');
  const emailInput = document.getElementById('forgot_email');

  if (!form || !emailInput) return; // nada a fazer se não encontrou

  // função de validação de email (boa prática, não perfeita)
  function isValidEmail(email) {
    if (!email || typeof email !== 'string') return false;
    // regex robusta e prática para validação básica
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
    return re.test(email.trim());
  }

  // garante que exista elemento de mensagem de erro logo após o grupo
  function ensureErrorElement() {
    // procura próximo irmão com classe .mn-email-error
    let err = emailInput.parentElement.parentElement.querySelector('.mn-email-error');
    if (!err) {
      err = document.createElement('div');
      err.className = 'mn-email-error';
      err.setAttribute('aria-live', 'polite');
      err.textContent = 'Formato de e-mail inválido.';
      // insere após o input-group
      const wrapper = emailInput.closest('.input-group') || emailInput.parentElement;
      wrapper.insertAdjacentElement('afterend', err);
    }
    return err;
  }

  // atualiza o estado visual de acordo com validade
  function setValidityState(isValid) {
    const inputGroup = emailInput.closest('.input-group');
    const err = ensureErrorElement();

    if (isValid) {
      if (inputGroup) inputGroup.classList.remove('invalid', 'shake');
      emailInput.classList.remove('input-invalid');
      err.classList.remove('active');
      emailInput.setAttribute('aria-invalid', 'false');
    } else {
      if (inputGroup) inputGroup.classList.add('invalid');
      emailInput.classList.add('input-invalid');
      err.classList.add('active');
      emailInput.setAttribute('aria-invalid', 'true');
    }
  }

  // valida em tempo real (a cada input)
  emailInput.addEventListener('input', function() {
    const ok = isValidEmail(emailInput.value);
    // se campo vazio não mostrar erro imediatamente — só ao blur/submit
    if (emailInput.value.trim() === '') {
      setValidityState(true);
      // esconde mensagem se existir
      const e = emailInput.closest('.input-group')?.parentElement?.querySelector('.mn-email-error');
      if (e) e.classList.remove('active');
      return;
    }
    setValidityState(ok);
  });

  // valida no blur (quando sai do campo)
  emailInput.addEventListener('blur', function() {
    const ok = isValidEmail(emailInput.value);
    setValidityState(ok);
  });

  // intercepta submit para prevenir envio quando inválido
  form.addEventListener('submit', function(ev) {
    const ok = isValidEmail(emailInput.value);
    if (!ok) {
      ev.preventDefault();
      ev.stopPropagation();

      // aplica shake para chamar atenção
      const inputGroup = emailInput.closest('.input-group');
      if (inputGroup) {
        inputGroup.classList.add('shake');
        // remove a classe após a animação para permitir reaplicar
        setTimeout(() => inputGroup.classList.remove('shake'), 400);
      }

      setValidityState(false);
      // foca o campo problemático
      emailInput.focus();
      return false;
    }

    // se ok, permite submit (mantendo seu comportamento PRG)
    return true;
  });
})();
</script>
</body>

</html>