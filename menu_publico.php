<?php
// MENU PÚBLICO 


///////////////////////////////// LOGIN DO PSICÓLOGO /////////////////////////////////////////

// Inicia a sessão e inclui a conexão
include "conn/conexao.php";

// Verifica se a sessão já foi iniciada, se não, inicia com o nome 'Mente_Renovada'
if (session_status() === PHP_SESSION_NONE) {
  session_name('Mente_Renovada');
  session_start();
}

// Resgata e apaga o flash, se existir
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Se o formulário de login foi enviado
if (isset($_POST['email'], $_POST['senha'], $_POST['CRP'])) {

  // Captura e sanitiza
  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
  $senha = trim($_POST['senha']);
  $CRP   = trim($_POST['CRP']);

  // Busca o psicólogo
  $sql  = "SELECT * FROM psicologo WHERE email = :email LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":email", $email);
  $stmt->execute();
  $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

  if (
    $resultado
    && password_verify($senha, $resultado['senha'])
    && password_verify($CRP,   $resultado['CRP'])
  ) {
    // Login OK: grava sessão e flash de sucesso
    $_SESSION['login_admin']  = $email;
    $_SESSION['psicologo_id'] = (int)$resultado['id'];
    $_SESSION['flash'] = [
      'type'    => 'success',
      'message' => 'Login realizado com sucesso!'
    ];
    header('Location: index.php');
    exit;
  } else {
    // Login falhou: flash de erro e abre o modal de login
    $_SESSION['flash'] = [
      'type'    => 'danger',
      'message' => 'Email, senha ou CRP inválidos. Tente novamente.'
    ];
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
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Menu Público</title>

  <!-- Bootstrap CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    /* ======== CSS ======== */
    html,
    body {
      margin: 0;
      padding: 0;
    }

    body {
      padding-top: 120px;
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
      padding: .5rem 1rem;
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
      display: flex !important; /* Para manter o gap funcionando */
      gap: 2rem; /* Espaço entre os próprios links */
      padding-left: 1.5rem; /* Afasta o grupo de links do logo */
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
    @media (max-width:991.98px) {
      .navbar-collapse {
        flex-direction: column;
        padding: 2rem 1rem;
        border-radius: 12px;
      }

      .navbar-nav {
        flex-direction: column !important;
        gap: 1rem;
        margin: 1rem 0;
      }

      .nav-buttons {
        flex-direction: column !important;
        gap: .8rem;
        margin-top: 1.5rem;
        width: 100%;
      }

      .registrar-text,
      .login-text {
        width: 100%;
        justify-content: center;
        font-size: 14px;
        padding: .6rem 1rem;
      }

      .navbar-toggler {
        margin-right: 1rem;
      }

      .nav-link {
        font-size: 16px;
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


  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container-fluid d-flex align-items-center">
      <!-- LOGO -->
      <a class="navbar-brand" href="index.php">
        <img src="image/MENTE_RENOVADA-LOGO.png" alt="Logo">
      </a>
      <!-- TOGGLER -->
      <button class="navbar-toggler ms-auto" type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- LINKS + BOTÕES -->
      <ul class="navbar-nav …">
        <?php if (isset($_SESSION['psicologo_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="sessao.php">Sessões</a></li>
          <li class="nav-item"><a class="nav-link" href="paciente.php">Pacientes</a></li>
      </ul>
    <?php endif; ?>
    <div class="d-flex align-items-center nav-buttons" style="min-width:250px">
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

  <!-- MODAL DE LOGIN -->
  <div class="modal fade" id="modalLogin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0" style="background:url('image/tela_login4.png') center/cover no-repeat;">
        <div class="modal-body p-4">
          <form action="menu_publico.php" method="POST" style="max-width:400px;margin:auto;">
            <label for="email" class="form-label" style="color:#DBA632;">Email:</label>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-envelope-fill text-dark"></i></span>
              <input type="email" name="email" id="email" class="form-control" required placeholder="Digite seu email.">
            </div>
            <label for="senha" class="form-label" style="color:#DBA632;">Senha:</label>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-lock-fill text-dark"></i></span>
              <input type="password" name="senha" id="senha" class="form-control" required placeholder="Digite sua senha.">
            </div>
            <label for="CRP" class="form-label" style="color:#DBA632;">CRP:</label>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-person-vcard text-dark"></i></span>
              <input type="password" name="CRP" id="CRP" class="form-control" maxlength="9" pattern="\d{2}/\d{1,6}" required placeholder="Digite seu CRP.">
            </div>
            <script>
              $(document).ready(() => $("#CRP").inputmask("99/999999"));
            </script>
            <div class="d-flex justify-content-between mt-4">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn" style="background:#DBA632;color:#fff;">Entrar</button>
            </div>
            <p class="text-center mt-3" style="color:#333;">
              Não possui uma conta?
              <a href="#" data-bs-toggle="modal" data-bs-target="#modalRegistro" data-bs-dismiss="modal" style="color:#DBA632;">Cadastre-se</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE REGISTRO -->
  <div class="modal fade" id="modalRegistro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0" style="background:url('image/Cadastro4.png') center/cover no-repeat;">
        <div class="modal-body p-4">
          <form action="cadastro.php" method="POST" enctype="multipart/form-data" style="max-width:400px;margin:auto;">
            <label for="nome" class="form-label" style="color:#DBA632;">Nome:</label>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-person-fill text-dark"></i></span>
              <input type="text" name="nome" id="nome" class="form-control" required placeholder="Digite seu nome.">
            </div>
            <label for="email" class="form-label" style="color:#DBA632;">Email:</label>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-envelope-fill text-dark"></i></span>
              <input type="email" name="email" id="email" class="form-control" required placeholder="Digite seu email.">
            </div>
            <label for="senha" class="form-label" style="color:#DBA632;">Senha:</label>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-lock-fill text-dark"></i></span>
              <input type="password" name="senha" id="senha" class="form-control" required placeholder="Digite sua senha.">
            </div>
            <label for="CRP" class="form-label" style="color:#DBA632;">CRP:</label>
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="bi bi-person-vcard text-dark"></i></span>
              <input type="password" name="CRP" id="CRP" class="form-control" maxlength="9" pattern="\d{2}/\d{1,6}" required placeholder="Digite seu CRP.">
            </div>
            <script>
              $(document).ready(() => $("#CRP").inputmask("99/999999"));
            </script>
            <div class="d-flex justify-content-between mt-4">
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn" style="background:#DBA632;color:#fff;">Cadastrar</button>
            </div>
            <p class="text-center mt-3" style="color:#333;">
              Já possui uma conta?
              <a href="#" data-bs-toggle="modal" data-bs-target="#modalLogin" data-bs-dismiss="modal" style="color:#DBA632;">Faça login</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const params = new URLSearchParams(window.location.search);
      if (params.get('login') === '1') {
        new bootstrap.Modal(document.getElementById('modalLogin')).show();
      }
    });

    // Fecha a mensagem de alerta após 5 segundos
    setTimeout(() => {
      const alertEl = document.querySelector('.alert-wrapper .alert');
      if (alertEl) {
        bootstrap.Alert.getOrCreateInstance(alertEl).close();
      }
    }, 5000);
  </script>
</body>

</html>