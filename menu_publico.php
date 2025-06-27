<!-- menu público -->

<?php
///////////////////////////////// LOGIN DO PSICÓLOGO /////////////////////////////////////////

// Inicia a sessão e inclui o arquivo de conexão com o banco de dados
include "conn/conexao.php";
///Para o login receber o cookie de acesso e não ficar revisitando a página de login.
session_name('Mente_Renovada');
session_start();

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
?>

<style>
  html,
  body {
    margin: 0;
    padding: 0;
  }

  body {
    padding-top: 90px;
  }

  .navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 90px;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.8);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }

  .navbar.scrolled {
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .navbar-brand img {
    height: 80px;
    width: auto;
    object-fit: contain;
    margin-right: 20px;
    transition: transform 0.3s ease-in-out, filter 0.3s ease-in-out;
  }

  .navbar-brand img:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 10px #DBA632);
  }

  .navbar-nav {
    display: flex;
    gap: 2rem;
    align-items: center;
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .nav-link {
    font-weight: bold;
    color: #333 !important;
    text-decoration: none !important;
    transition: color 0.3s;
  }

  .nav-link:hover {
    color: #DBA632 !important;
  }

  .login-text {
    font-size: 15px;
    background-color: #DBA632;
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 160px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: color 0.3s ease-in-out, transform 0.3s ease-in-out;
  }

  .login-text:hover {
    color: #b7861e;
    transform: scale(1.05);
  }

  .nav-text{
    font-size: 15px;
    background-color:white;
    color:black;
    padding: 0.6rem 1.2rem;
    border-radius: 160px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: color 0.3s ease-in-out, transform 0.3s ease-in-out;
    text-decoration: none !important;
    margin-left: 10px;
  }

  .nav-text:hover{
    background-color: white;
     color: red;
    transform: scale(1.05);
  }

  /* NOVO BLOCO - para alinhar ícones e campos em qualquer tamanho de tela */
  .input-group {
    align-items: center;
  }

  .input-group .input-group-text {
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-right: 0;
  }

  .input-group .form-control {
    height: 45px;
    font-size: 0.95rem;
  }

  /* MOBILE */
  @media (max-width: 575.98px) {
    .modal-dialog {
      margin: 1rem auto;
    }

    .modal-content {
      min-height: auto !important;
    }

    .modal-body {
      padding: 1.5rem 1rem !important;
    }

    .modal-body .btn {
      width: 100%;
      font-size: 1rem;
    }

    .modal-body .d-flex.justify-content-between {
      flex-direction: column;
      align-items: stretch;
      gap: 0.75rem;
    }

    /* Espaçamento entre botões */
    .modal-body .btn {
      min-width: 120px;
      padding: 0.35rem 0.8rem;
      font-size: 0.9rem;
    }

    /* Aproximar botões cancelar e cadastrar/entrar */
    .modal-body .d-flex.justify-content-between {
      gap: 0.75rem;
    }
  }

  /* TABLET e MOBILE menu */
  @media (max-width: 991.98px) {
    .navbar-collapse {
      background-color: #fff;
      padding: 1rem;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .navbar-nav {
      flex-direction: column;
      gap: 1rem;
    }

    .navbar-nav .nav-link {
      padding: 0.5rem 1rem;
      font-size: 1rem;
    }

    .login-text {
      margin-top: 0.5rem;
      justify-content: center;
    }

    .navbar-toggler {
      margin-left: auto;
    }

    .nav-buttons {
      flex-direction: column;
      justify-content: center;
      width: 100%;
      margin-top: 1rem;
    }

    .nav-buttons .nav-link {
      padding: 0.5rem 0;
      width: 100%;
      text-align: center;
    }

    .nav-buttons .login-text {
      justify-content: center;
      width: 100%;
    }
  }

  .modal-body form {
    margin: 0 auto;
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .modal-body label {
    text-align: left;
    width: 100%;
    margin-bottom: 0.2rem;
    margin-top: 0.8rem;
  }

  .input-group {
    width: 100%;
  }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-white">
  <div class="container-fluid d-flex justify-content-between align-items-center px-4">

    <!-- Logo (esquerda) -->
    <a href="index.php" class="navbar-brand">
      <img src="image/MENTE_RENOVADA-LOGO.png" alt="Logotipo" />
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>


    <div class="collapse navbar-collapse justify-content-between" id="navbarSupportedContent">
      <!-- Links principais (centro) -->
      <div class="d-flex flex-grow-1 justify-content-center">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="sessao.php">Sessões</a></li>
          <li class="nav-item"><a class="nav-link" href="paciente.php">Pacientes</a></li>
        </ul>
      </div>

      <!-- Os botões aparecem quando não há um login realizado -->
      <?php if (!isset($_SESSION['login_admin'])) {
        // Exibe o login armazenado (único) na sessão 
      ?>
        <!-- Botões (direita) -->
        <div class="nav-buttons d-flex align-items-center gap-3 flex-shrink-0">
          <a class="nav-link" data-bs-toggle="modal" data-bs-target="#modalRegistro">Registre-se</a>
          <a class="nav-link" data-bs-toggle="modal" data-bs-target="#modalLogin">
            <span class="login-text">
              <i class="bi bi-person-fill perfil-icon"></i> Entrar
            </span>
          </a>
        </div>
      <?php } ?>

      <li class="nav-buttons d-flex align-items-center gap-3 flex-shrink-0">

      <!-- Email do psicólogo no navbar -->
        <?php //ocorre se o usuario estiver logado
        if (isset($_SESSION['login_admin'])) { ?>
          <a href="index.php">
            <button type="button" class="btn text-light" style="cursor: default; background-color: #DBA632; border-radius: 160px; padding: 0.6rem 1.2rem;">
              <?php echo ($_SESSION['login_admin']); ?>!
            </button>
          </a>
        <?php } ?>
      </li>
      <!-- Botão logout -->
        <!-- Caso a super global SESSION receber o login_admin do usuário ele exibirá o logout para a tela do usuário -->
            <?php if (isset($_SESSION['login_admin'])): ?>
                <a class="nav-text bi bi-box-arrow-right" href="logout.php">
                  Logout
                </a>
            <?php endif; ?>
    </div>
</nav>
</div>
<!-- Script: escurecer navbar ao rolar -->
<script>
  window.addEventListener('scroll', function() {
    const nav = document.querySelector('.navbar');
    nav.classList.toggle('scrolled', window.scrollY > 50);
  });
</script>

<!-- Modal de Login -->
<div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0" style="background: url('image/tela_login.png') center/cover no-repeat;">
      <div class="modal-body p-0 d-flex flex-column align-items-center justify-content-center" style="padding: 2rem;">

        <!-- Formulário de Login -->
        <form action="menu_publico.php" method="POST" enctype="multipart/form-data" style="width: 100%; max-width: 400px;">

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
    <div class="modal-content border-0" style="background: url('image/tela_cadastro.png') center/cover no-repeat;">
      <div class="modal-body p-0 d-flex flex-column align-items-center justify-content-center" style="padding: 2rem;">

        <!-- Formulário de Registro -->
        <form action="cadastro.php" method="POST" enctype="multipart/form-data" style="width: 100%; max-width: 400px;">

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