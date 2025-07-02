<!-- menu público -->

<?php

///////////////////////////////// LOGIN DO PSICÓLOGO /////////////////////////////////////////

// Inicia a sessão e inclui o arquivo de conexão com o banco de dados
include "conn/conexao.php";

if (session_status() === PHP_SESSION_NONE) {
  session_name('Mente_Renovada');
  session_start();
}

// Verifica se o formulário foi enviado

if (isset($_POST['email']) || isset($_POST['senha']) || isset($_POST['CRP'])) {

  // Variáveis que recebem o valor do formulário
  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); // Usa filter_var para validar o email
  $senha = (trim($_POST['senha']));
  $CRP = (trim($_POST['CRP']));

  // Prepara a consulta SQL para verificar o usuário de entrada do psicólogo
  $sql = "SELECT * FROM psicologo WHERE email = :email LIMIT 1"; // O LIMIT 1 garante que apenas um registro seja retornado
  $stmt = $conn->prepare($sql);
  $stmt->bindParam("email", $email);
  $stmt->execute();

  $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($resultado && password_verify($senha, $resultado['senha']) && password_verify($CRP, $resultado['CRP'])) {
    // Login autorizado
    $_SESSION['login_admin'] = $email;
    $_SESSION['nome_de_sessao'] = session_name();

    echo "<script>
            alert('Seja bem-vindo $email!');
            window.location.href = 'index.php';
        </script>";
  } else {
    // Dados inválidos
    echo "<script>
            alert('Email, senha ou CRP inválidos. Por favor, tente novamente');
            window.location.href = 'index.php';
        </script>";
  }

  $conn = null; // Fecha a conexão com o banco
}

//////////////////////////////// IMAGEM DE PERFIL /////////////////////////////////////////

// Se o psicólogo está logado, busca a foto de perfil (mesma lógica de perfil_ps.php)
if (isset($_SESSION['login_admin'])) {
  $email = $_SESSION['login_admin'];

  $stmt = $conn->prepare("
      SELECT foto_perfil
      FROM psicologo
      WHERE email = :email
      LIMIT 1
    ");
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $diretorio = 'image/';        // mesma pasta usada em perfil_ps.php
  $arquivo   = $row['foto_perfil'] ?? '';
  $fotoPerfilPath = (!empty($arquivo) && file_exists($diretorio . $arquivo))
    ? $diretorio . $arquivo
    : $diretorio . 'default.png';
}

?>
<style>
  html,
  body {
    margin: 0;
    padding: 0;
  }

  body {
    padding-top: 120px;
  }


  .navbar {
    position: fixed;
    top: 0;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    padding: 0.5rem 1rem;
  }


  /* LOGO DA MENTE RENOVADA */
  .navbar-brand img {
    height: 90px;
    object-fit: contain;
    transition: transform 0.3s ease-in-out, filter 0.3s ease-in-out;
  }

  .navbar-brand img:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 10px #DBA632);
  }

  .nav-link {
    font-weight: bold;
    color: #333 !important;
    transition: color 0.3s;
  }

  .nav-link:hover {
    color: #DBA632 !important;
  }

  .perfil-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .perfil-img:hover {
    transform: scale(1.15);
    box-shadow: 0 0 10px rgba(219, 166, 50, 0.5);
  }

  .registrar-text,
  .login-text {
    font-size: 15px;
    padding: 0.6rem 1.2rem;
    border-radius: 160px;
    border: 2px solid #DBA632;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: bold;
    transition: all 0.3s ease-in-out;
  }

  .registrar-text {
    background-color: #DBA632;
    color: white;
  }

  .registrar-text:hover {
    background-color: white;
    color: #DBA632;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(219, 166, 50, 0.5);
  }

  .login-text {
    background-color: white;
    color: #DBA632;
  }

  .login-text:hover {
    background-color: #DBA632;
    color: white;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(219, 166, 50, 0.5);
  }

  /* RESPONSIVIDADE */
  @media (max-width: 991.98px) {
    .navbar-collapse {
      display: flex;
      flex-direction: column;
      align-items: center;
      background-color: white;
      padding: 2rem 1rem;
      border-radius: 12px;
      margin-top: 1rem;
    }

    .navbar-brand img {
      height: 40px;
      margin-left: 1rem;
    }

    .navbar-nav {
      flex-direction: column !important;
      align-items: center;
      gap: 1rem;
    }

    .nav-buttons {
      flex-direction: column !important;
      gap: 0.8rem;
      margin-top: 1.5rem;
      width: 100%;
      align-items: center;
    }

    .registrar-text,
    .login-text {
      width: 100%;
      justify-content: center;
      font-size: 14px;
      padding: 0.6rem 1rem;
    }

    .navbar-toggler {
      margin-right: 1rem;
    }

    .nav-link {
      font-size: 16px;
    }
  }
</style>

<!-- NAVBAR FUNCIONAL -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
  <div class="container-fluid">

    <!-- LOGO -->
    <a class="navbar-brand" href="index.php" style="width: 120px;">
      <img src="image/MENTE_RENOVADA-LOGO.png" alt="Logo" style="height: 90px; object-fit: contain;">
    </a>

    <!-- TOGGLER -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- CONTEÚDO DA NAVBAR -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center w-100 gap-3">

        <!-- LINKS -->
        <ul class="navbar-nav flex-lg-row flex-column align-items-center justify-content-center flex-grow-1 gap-3">
          <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="sessao.php">Sessões</a></li>
          <li class="nav-item"><a class="nav-link" href="paciente.php">Pacientes</a></li>
        </ul>

        <!-- PERFIL OU BOTÕES -->
        <div class="d-flex align-items-center justify-content-center gap-2">
          <?php if (!isset($_SESSION['login_admin'])): ?>
            <a class="nav-link registrar-btn" data-bs-toggle="modal" data-bs-target="#modalRegistro">
              <span class="registrar-text"><i class="bi bi-person-plus-fill"></i> Registre-se</span>
            </a>
            <a class="nav-link" data-bs-toggle="modal" data-bs-target="#modalLogin">
              <span class="login-text"><i class="bi bi-person-fill"></i> Entrar</span>
            </a>
          <?php else: ?>
            <a href="perfil_ps.php" title="Meu Perfil">
              <img src="<?= htmlspecialchars($fotoPerfilPath); ?>" alt="Foto de Perfil" class="perfil-img" />
            </a>
          <?php endif; ?>
        </div>

      </div>
    </div>

  </div>
</nav>





<!-- Modal de Login -->
<div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0" style="background: url('image/tela_login4.png') center/cover no-repeat;">
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
    <div class="modal-content border-0" style="background: url('image/Cadastro4.png') center/cover no-repeat;">
      <div class="modal-body p-0 d-flex flex-column align-items-center justify-content-center" style="padding: 2rem;">

        <!-- Formulário de Registro -->
        <form action="cadastro.php" method="POST" enctype="multipart/form-data" style="width: 90%; max-width: 400px; margin-top: 60px;">

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


<!-- Função JS (caso queira abrir modal por script) -->
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