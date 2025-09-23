<?php
// MENU PÚBLICO 

///////////////////////////////// LOGIN DO PSICÓLOGO /////////////////////////////////////////

// Inicia a sessão e inclui a conexão
require_once __DIR__ . '/conn/conexao.php'; // espera prover $conn (PDO)

// Funções utilitárias (helpers.php)
require_once __DIR__ . '/helpers.php';

// Autoload / wrapper de envio de email (send_email.php)
require_once __DIR__ . '/send_email.php';

// Agora cuida da sessão 
if (session_status() === PHP_SESSION_NONE) {
  session_name('Mente_Renovada');
  session_start();
}

// Resgata e apaga o flash, se existir
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Se o formulário de login foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['senha'], $_POST['CRP'])) {

  // Captura e sanitiza
  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
  $senha = trim($_POST['senha']);
  $CRP   = trim($_POST['CRP']);

  try {
    // Busca o psicólogo
    $sql  = "SELECT * FROM psicologo WHERE email = :email LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
      $resultado
      && (int)$resultado['ativo'] === 1
      && password_verify($senha, $resultado['senha'])
      && password_verify($CRP,   $resultado['CRP'])
    ) {

      // --- INÍCIO: fluxo de verificação por token (substitui a criação direta da sessão) ---

      $psicologo_id = (int) $resultado['id'];

      // ---------- DETECTA se a coluna token_type existe (compatibilidade) ----------
      // Se existir, vamos usar token_type = 'confirmation' / 'persistent' para separar responsabilidades.
      $q = $conn->prepare("
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'login_tokens'
          AND column_name = 'token_type'
        LIMIT 1
      ");
      $q->execute();
      $has_token_type = $q->fetchColumn() !== false;

      // 1) checar cookie existente (token persistente)
      $cookieToken = $_COOKIE['login_token'] ?? null;
      $cookie_valid = false;

      if ($cookieToken) {
        $hash = token_hash($cookieToken);

        // busca token — se token_type existe, retornará também token_type
        $selSql = "
          SELECT id, expires_at, used" . ($has_token_type ? ", token_type" : "") . "
          FROM login_tokens
          WHERE psicologo_id = :pid AND token_hash = :h
          LIMIT 1
        ";
        $sel = $conn->prepare($selSql);
        $sel->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
        $sel->bindParam(':h', $hash);
        $sel->execute();
        $rowToken = $sel->fetch(PDO::FETCH_ASSOC);

        if ($rowToken && (int)$rowToken['used'] === 0 && strtotime($rowToken['expires_at']) > time()) {
          if ($has_token_type) {
            // exige token_type = 'persistent' para considerar cookie válido
            if (isset($rowToken['token_type']) && $rowToken['token_type'] === 'persistent') {
              $cookie_valid = true;
            }
          } else {
            // sem token_type, aceita qualquer token válido (compatibilidade retroativa)
            $cookie_valid = true;
          }
        }
      }

      if ($cookie_valid) {
        // cookie válido -> cria sessão normalmente
        $_SESSION['login_admin']  = $email;
        $_SESSION['psicologo_id'] = $psicologo_id;
        $_SESSION['flash'] = [
          'type'    => 'success',
          'message' => 'Login realizado com sucesso!.'
        ];
        header('Location: index.php');
        exit;
      }

      // se chegou aqui -> cookie ausente/inválido => gerar token de CONFIRMAÇÃO e enviar e-mail

      $token = generate_token(16); // gera 32 chars hex (supondo implementação em helpers)
      $hash  = token_hash($token);
      $expires_at = end_of_day_datetime(); // formato 'Y-m-d H:i:s' (sua helper: expira à meia-noite)

      // ---------- INVALIDAÇÃO CONSERVADORA ----------
      // NÃO invalidamos tokens persistentes. Se token_type existir, invalidamos apenas tokens 'confirmation'.
      if ($has_token_type) {
        $upd = $conn->prepare("UPDATE login_tokens SET used = 1 WHERE psicologo_id = :pid AND token_type = 'confirmation' AND used = 0");
        $upd->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
        $upd->execute();
      } else {
        // Sem token_type: optamos por NÃO invalidar tokens automaticamente para não quebrar tokens persistentes antigos.
        // Se desejar um comportamento diferente (ex: invalidar tokens expirados) ajuste aqui.
      }

      // ---------- Inserir novo token de CONFIRMAÇÃO ----------
      if ($has_token_type) {
        $ins = $conn->prepare("
          INSERT INTO login_tokens (psicologo_id, token_hash, expires_at, token_type, used)
          VALUES (:pid, :h, :e, 'confirmation', 0)
        ");
        $ins->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
        $ins->bindParam(':h', $hash);
        $ins->bindParam(':e', $expires_at);
        $ins->execute();
      } else {
        // tabela antiga sem token_type
        $ins = $conn->prepare("
          INSERT INTO login_tokens (psicologo_id, token_hash, expires_at, used)
          VALUES (:pid, :h, :e, 0)
        ");
        $ins->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
        $ins->bindParam(':h', $hash);
        $ins->bindParam(':e', $expires_at);
        $ins->execute();
      }

      // Montar link de verificação (robusto para subpastas como /clinica-psicologia-web)
      $scheme = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
      ) ? 'https' : 'http';

      $host = $_SERVER['HTTP_HOST']; // ex: "localhost" ou "localhost:8000"

      // pega a pasta onde o script atual está; ex: "/clinica-psicologia-web"
      $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

      // normaliza casos em que dirname retorna "." ou "/"
      if ($basePath === '.' || $basePath === '/' || $basePath === '\\') {
        $basePath = '';
      }

      // monta o caminho final (sem duplicar barras)
      $verifyLink = $scheme . '://' . $host . $basePath . '/verify_login.php'
        . '?t=' . urlencode($token)
        . '&u=' . urlencode($psicologo_id);

      // -----------------------
      // Preparar e-mail (DEFINIÇÃO ANTES DO ENVIO)
      // -----------------------

      // Nome do destinatário (segurança: fallback vazio)
      $toName = $resultado['nome'] ?? '';

      // Assunto do e-mail
      $subject = 'Confirme seu login - Mente Renovada';

      // Corpo HTML do e-mail (escape para evitar injeção)
      $htmlBody = "<p>Olá " . htmlspecialchars($toName) . ",</p>"
        . "<p>Detectamos seu acesso. Para confirmar seu login hoje, clique no link abaixo (válido até meia-noite):</p>"
        . "<p><a href=\"" . htmlspecialchars($verifyLink) . "\" target=\"_blank\" rel=\"noopener\">Confirmar meu login</a></p>"
        . "<p>Link direto (copiar/colar):<br><small>" . htmlspecialchars($verifyLink) . "</small></p>"
        . "<p>Se preferir, copie e cole este código no formulário de verificação: <strong>" . htmlspecialchars($token) . "</strong></p>"
        . "<p>Se não foi você, ignore este e-mail.</p>";

      // Corpo texto (fallback) — útil para clientes que não renderizam HTML ou para logs
      $plainBody = "Olá {$toName},\n\n"
        . "Para confirmar seu login hoje, acesse este link (válido até meia-noite):\n"
        . "{$verifyLink}\n\n"
        . "Ou cole este código no formulário: {$token}\n\n"
        . "Se não foi você, ignore.";

      // Observação: verifique a assinatura da sua função send_verification_email em send_email.php.
      // Aqui eu chamo com 4 parâmetros (toEmail, toName, subject, htmlBody) — se sua função aceitar plainBody, adicione.
      $sent = send_verification_email($email, $toName, $subject, $htmlBody);

      // Se o seu wrapper retornar false/true conforme sucesso, usaremos isso para o flash
      if ($sent) {
        $_SESSION['flash'] = [
          'type' => 'info',
          'message' => 'Enviamos um e-mail com um código/link para confirmar seu login. Verifique sua caixa de entrada.'
        ];
      } else {
        // Sugestão: registre o erro em log para diagnosticar (ex.: error_log ou um arquivo)
        error_log("Falha ao enviar e-mail de verificação para {$email}");
        $_SESSION['flash'] = [
          'type' => 'warning',
          'message' => 'Não foi possível enviar o e-mail de confirmação. Tente novamente ou contate o suporte.'
        ];
      }

      // redireciona para a página de verificação (lá o usuário pode colar o token ou usar o link)
      header('Location: verify_login.php?sent=1&u=' . $psicologo_id);
      exit;
    } else {
      // credenciais inválidas (mensagem genérica)
      $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Credenciais inválidas ou conta inativa.'];
      header('Location: index.php?login=1');
      exit;
    }
  } catch (Exception $e) {
    error_log("menu_publico login error: " . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro interno. Tente novamente.'];
    header('Location: index.php?login=1');
    exit;
  }
}

//////////////////////////////// IMAGEM DE PERFIL /////////////////////////////////////////

// Se estiver logado, busca o caminho da foto de perfil
if (isset($_SESSION['login_admin'])) {
  $email = $_SESSION['login_admin'];
  $stmt  = $conn->prepare("
        SELECT foto_perfil
        FROM psicologo
        WHERE email = :email
        LIMIT 1
    ");
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $diretorio      = 'image/';  // pasta de imagens
  $arquivo        = $row['foto_perfil'] ?? '';
  $fotoPerfilPath = (!empty($arquivo) && file_exists($diretorio . $arquivo))
    ? $diretorio . $arquivo
    : $diretorio . 'default.png';
} else {
  $fotoPerfilPath = 'image/default.png';
}
?>
<!-- ===================== HTML / NAVBAR / MODALS abaixo (seu código original) ===================== -->
<style>
  /* ======== CSS ======== */
  html,
  body {
    margin: 0;
    padding: 0;
  }

  /* Mensagem de aviso */
  /* Posiciona a wrapper e deixa-a apenas do tamanho do conteúdo */
  .alert-wrapper {
    position: fixed;
    top: 1rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2000;
    display: inline-block;
    /* só o tamanho do alerta */
  }

  /* O próprio alerta */
  .alert-wrapper .alert {
    position: relative;
    display: inline-block;
    /* largura ajusta ao texto */
    box-sizing: border-box;
    white-space: normal;
    /* permite quebra de linha */
    word-wrap: break-word;

    /* padding: topo | direita | base | esquerda */
    padding: 0.5rem 2.5rem 0.5rem 0.75rem;
    /* 2.5rem reserva espaço para o X sem impactar o conteúdo */

    font-size: 0.95rem;
    border-radius: 0.375rem;
    /* cantos suaves como na sua imagem */
  }

  /* Botão de fechar */
  .alert-wrapper .btn-close {
    position: absolute;
    top: 50%;
    right: 0.5rem;
    /* afasta um pouco da borda interna */
    transform: translateY(-50%);
    background: none !important;
    border: none;
    padding: 0;
    font-size: 1rem;
    line-height: 1;
    color: #000;
    opacity: .6;
  }

  /* Insere o “×” puro e remove qualquer SVG */
  .alert-wrapper .btn-close::before {
    content: "×";
  }

  .alert-wrapper .btn-close svg {
    display: none !important;
  }

  /* Estilo do navbar */
  .navbar {
    position: fixed;
    top: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 6px rgba(0, 0, 0, .1);
    z-index: 1000;
    padding: .1rem 1rem;
  }

  /* Logo da Mente Renovada */
  .navbar-brand img {
    height: 90px;
    object-fit: contain;
    transition: transform .3s, filter .3s;
  }

  .navbar-brand img:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 10px #DBA632);
  }

  /* Links do menu */
  /* Início, Sessão e Paciente */
  .navbar-nav {
    display: flex !important;
    /* Para manter o gap funcionando */
    gap: 2rem;
    /* Espaço entre os próprios links */
    padding-left: 0.5rem;
    /* Afasta o grupo de links do logo */
  }

  .navbar .nav-link {
    position: relative;
    display: inline-block;
    font-size: 15px;
    font-weight: bold;
    text-transform: uppercase;
    color: #333 !important;
    padding: .5rem .75rem;
    overflow: hidden;
    transition: color .2s, transform .2s;
  }

  /* Animação dos links */
  .navbar .nav-link::after {
    content: "";
    position: absolute;
    left: 50%;
    bottom: 0;
    width: 0;
    height: 2px;
    background: #DBA632;
    transition: width .3s, left .3s;
  }

  .navbar .nav-link:hover {
    color: #DBA632 !important;
  }

  .navbar .nav-link:hover::after {
    left: 0;
    width: 100%;
  }

  /* Animação do cursor nos botões */
  @keyframes navBounce {

    0%,
    100% {
      transform: scale(1)
    }

    50% {
      transform: scale(1.1)
    }
  }


  .navbar .nav-link:active,
  .navbar .nav-link:focus {
    animation: navBounce .3s ease;
    outline: none;
  }

  /* Imagem do Perfil do psicólogo */
  .perfil-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 60px;
    transition: transform .3s, box-shadow .3s;
    margin-left: 60px;
  }

  .perfil-img:hover {
    transform: scale(1.15);
    box-shadow: 0 0 10px rgba(219, 166, 50, .5);
  }

  /* Botões de registrar e login */
  .registrar-text,
  .login-text {
    font-size: 15px;
    padding: .6rem 1.2rem;
    border-radius: 160px;
    border: 2px solid #DBA632;
    display: flex;
    align-items: center;
    gap: .4rem;
    font-weight: bold;
    transition: all .3s;
  }

  .registrar-text {
    background: #DBA632;
    color: #fff;
  }

  .registrar-text:hover {
    background: #fff;
    color: #DBA632;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(219, 166, 50, .5);
  }

  .login-text {
    background: #fff;
    color: #DBA632;
  }

  .login-text:hover {
    background: #DBA632;
    color: #fff;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(219, 166, 50, .5);
  }

  /* Responsividade para celular e tablet */
  @media (max-width: 991.98px) {
    .navbar-collapse.show {
      padding-bottom: 1.6rem;
      /* Garante espaço extra para o avatar 'sair' sem sobrepor conteúdo */
    }

    .navbar-collapse.show .perfil-wrap {
      /* Mantém o wrapper normal, sem impacto no fluxo */
      position: relative;
    }

    .navbar-collapse.show .perfil-img {
      transform: translateY(10px);
      /* Centralização e aparência */
      display: block;
      margin: 0 auto;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
    }

    /* Empilha e centraliza os itens do menu */
    .navbar-nav {
      flex-direction: column !important;
      align-items: center;
      width: 100%;
      text-align: center;
      gap: 1rem;
      padding: 0;
      margin: 0;
    }

    /* Botões de registro/login e avatar dentro do mesmo fluxo */
    .nav-buttons {
      display: flex !important;
      flex-direction: column;
      align-items: center;
      width: 100%;
      gap: 1rem;
      margin-top: 1.5rem;
      padding: 0;
    }

    .nav-buttons .nav-link {
      display: flex !important;
      /* transforma o link num flex container */
      justify-content: center !important;
      /* centraliza horizontalmente */
      align-items: center;
      /* centraliza verticalmente */
      width: 100%;
      padding: 0.6rem 0;
      font-size: 14px;
      text-align: center;
      /* fallback para navegadores sem flex */
    }


    /* Links principais */
    .navbar-nav .nav-link {
      text-align: center;
    }


    /* Centraliza o avatar */
    .perfil-img {
      width: 60px;
      height: 60px;
      margin: 0 auto;
      margin-bottom: 20px;
    }

    /* Ajuste estético do toggler */
    .navbar-toggler {
      margin-right: 1rem;
    }
  }
</style>
</head>

<body>

  <!-- ALERTA FIXO NO TOPO -->
  <?php if ($flash): ?>
    <div class="alert-wrapper">
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-0" role="alert">
        <?= $flash['message'] /* aqui o <strong> é renderizado como HTML */ ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
      </div>
    </div>
  <?php endif; ?>


  <body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
      <div class="container-fluid px-0 d-flex align-items-center">
        <!-- LOGO -->
        <a class="navbar-brand" href="index.php">
          <img src="image/MENTE_RENOVADA-LOGO.png" alt="Logo">
        </a>

        <!-- TOGGLER -->
        <button class="navbar-toggler" type="button"
          data-bs-toggle="collapse"
          data-bs-target="#mainNavbar"
          aria-controls="mainNavbar"
          aria-expanded="false"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- CONTEÚDO DO TOGGLER/NAVBAR -->
        <div class="collapse navbar-collapse" id="mainNavbar">
          <!-- LINKS PRINCIPAIS -->
          <ul class="navbar-nav ms-auto">
            <?php if (isset($_SESSION['psicologo_id'])): ?>
              <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
              <li class="nav-item"><a class="nav-link" href="sessao.php">Sessões</a></li>
              <li class="nav-item"><a class="nav-link" href="paciente.php">Pacientes</a></li>
            <?php endif; ?>
          </ul>

          <!-- BOTÕES DE REGISTRO / LOGIN -->
          <div class="nav-buttons d-flex ms-auto">
            <?php if (!isset($_SESSION['login_admin'])): ?>
              <a class="nav-link me-2" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                <span class="registrar-text"><i class="bi bi-person-plus-fill"></i> Registre-se</span>
              </a>
              <a class="nav-link" data-bs-toggle="modal" data-bs-target="#modalLogin">
                <span class="login-text"><i class="bi bi-person-fill"></i> Entrar</span>
              </a>
            <?php else: ?>
              <a href="perfil_ps.php" title="Meu Perfil">
                <img src="<?= htmlspecialchars($fotoPerfilPath) ?>" class="perfil-img" alt="Perfil">
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>






    <!-- Modal de Login -->
    <div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="background: url('image/Login.png') center/cover no-repeat;">
          <div class="modal-body p-0 d-flex flex-column align-items-center justify-content-center" style="padding: 2rem;">

            <!-- Formulário de Login -->
            <form action="menu_publico.php" method="POST" enctype="multipart/form-data" style="width: 90%; max-width: 400px; margin-top: 70px;">

              <!-- Email -->
              <label for="email" class="form-label" style="color: #DBA632;">Email:</label>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-envelope-fill text-dark"></i></span>
                <input type="email" name="email" id="email" class="form-control" required placeholder="Digite seu email.">
              </div>

              <!-- Campo Senha -->
              <label for="senha" style="color: #DBA632;">Senha:</label>
              <div class="input-group mb-3">
                <span class="input-group-text">
                  <i class="bi bi-lock-fill text-dark" aria-hidden="true"></i>
                </span>
                <input type="password" name="senha" id="senha" class="form-control" required autocomplete="off" placeholder="Digite sua senha.">
              </div>
              <!-- Campo CRP -->
              <label for="CRP" style="color: #DBA632;">CRP:</label>
              <div class="input-group mb-3">
                <span class="input-group-text">
                  <i class="bi bi-person-vcard text-dark" aria-hidden="true"></i>
                </span>
                <input type="password" name="CRP" id="CRP" class="form-control" maxlength="9" pattern="\d{2}/\d{1,6}" required placeholder="Digite seu CRP.">
              </div>
              <!-- InputMask para CRP -->
              <script>
                $(document).ready(function() {
                  $("#CRP").inputmask("99/999999");
                });
              </script>

              <!-- Botões -->
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn" style="background-color: #DBA632; color: white;">Entrar</button>
              </div>

              <br><br><br>

              <!-- Link para cadastro -->
              <p class="text-center mt-3" style="color: #333;">
                Não possui uma conta?
                <a href="#" data-bs-toggle="modal" data-bs-target="#modalRegistro" data-bs-dismiss="modal" style="color: #DBA632;">Cadastre-se</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>



    <!-- Modal de Registro -->
    <div class="modal fade" id="modalRegistro" tabindex="-1" aria-labelledby="modalRegistroLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="background: url('image/Cadastro.png') center/cover no-repeat;">
          <div class="modal-body p-0 d-flex flex-column align-items-center justify-content-center" style="padding: 2rem;">

            <!-- Formulário de Registro -->
                  <form action="cadastro_confirm.php" method="POST" enctype="multipart/form-data" style="width: 90%; max-width: 400px; margin-top: 60px;">

              <!-- Nome -->
              <label for="nome" class="form-label" style="color: #DBA632;">Nome:</label>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-person-fill text-dark"></i></span>
                <input type="text" name="nome" id="nome" class="form-control" required placeholder="Digite seu nome.">
              </div>

              <!-- Email -->
              <label for="email" class="form-label" style="color: #DBA632;">Email:</label>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-envelope-fill text-dark"></i></span>
                <input type="email" name="email" id="email" class="form-control" required placeholder="Digite seu email.">
              </div>

              <!-- Senha -->
              <label for="senha" class="form-label" style="color: #DBA632;">Senha:</label>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-lock-fill text-dark"></i></span>
                <input type="password" name="senha" id="senha" class="form-control" required placeholder="Digite sua senha.">
              </div>

              <!-- CRP -->
              <label for="CRP" class="form-label" style="color: #DBA632;">CRP:</label>
              <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-person-vcard text-dark"></i></span>
                <input type="password" name="CRP" id="CRP" class="form-control" maxlength="9" pattern="\d{2}/\d{1,6}" required placeholder="Digite seu CRP.">
              </div>

              <!-- InputMask para CRP -->
              <script>
                $(document).ready(function() {
                  $("#CRP").inputmask("99/999999");
                });
              </script>

              <!-- Botões -->
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn" style="background-color: #DBA632; color: white;">Cadastrar</button>
              </div>

              <!-- Link para login -->
              <p class="text-center mt-3" style="color: #333; margin-top: 70px !important;">
                Já possui uma conta?
                <a href="#" data-bs-toggle="modal" data-bs-target="#modalLogin" data-bs-dismiss="modal" style="color: #DBA632;">Faça login</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>


    <!-- Função JS para abrir o modal por script -->
    <script>
      function abrirModal() {
        const modal = new bootstrap.Modal(document.getElementById('modalLogin'));
        modal.show();
      }
    </script>

    <!-- Função JS para redirecionar o usuário da página de cadastro para o modal de login  -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('login') === 'abrir') {
          const loginModal = new bootstrap.Modal(document.getElementById('modalLogin'));
          loginModal.show();
        }
      });
    </script>