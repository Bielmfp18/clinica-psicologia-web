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
    padding-top: 90px;
  }

  .navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 90px;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    padding: 0 2rem;
    z-index: 1000;
  }

  .navbar-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-left: 2rem;
    /* espaçamento à esquerda do ícone */
  }

  .navbar-left,

  /* Logo animação */
  .navbar-left a img {
    height: 80px;
    object-fit: contain;
    transition: transform 0.3s ease-in-out, filter 0.3s ease-in-out;
  }

  .navbar-left a img:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 10px #DBA632);
  }

  /* Animação da imagem de Perfil */
  .perfil-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .perfil-img:hover {
    transform: scale(1.15);
    box-shadow: 0 0 10px rgba(219, 166, 50, 0.5);
  }


  .navbar-center {
    display: flex;
    justify-content: center;
  }

  .navbar-center ul {
    display: flex;
    gap: 2rem;
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .navbar-center .nav-link {
    font-weight: bold;
    color: #333 !important;
    text-decoration: none;
    transition: color 0.3s;
  }

  .navbar-center .nav-link:hover {
    color: #DBA632 !important;
  }

  .nav-buttons {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  /* Animação do botão de login */
  .login-text {
    font-size: 15px;
    background-color: white;
    color: #DBA632;
    padding: 0.6rem 1.2rem;
    border-radius: 160px;
    border: 2px solid #DBA632;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.3s ease-in-out;
    font-weight: bold;
  }

  .login-text:hover {
    background-color: #DBA632;
    color: white;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(219, 166, 50, 0.5);
  }

  /* Animação do botão registre-se */
  .registrar-text {
    font-size: 15px;
    background-color:  #DBA632;
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 160px;
    border: 2px solid #DBA632;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.3s ease-in-out;
    font-weight: bold;
  }

  .registrar-text:hover {
    background-color: white;
    color: #DBA632;
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(219, 166, 50, 0.5);
  }

  .navbar-right img {
    border-radius: 50%;
    width: 50px;
    height: 50px;
    object-fit: cover;
  }

  /* Responsivo */
  @media (max-width: 768px) {
    .navbar {
      grid-template-columns: 1fr auto 1fr;
      flex-wrap: wrap;
      height: auto;
      padding: 1rem;
    }

    .navbar-center ul {
      flex-direction: column;
      gap: 1rem;
    }

    .nav-buttons {
      flex-direction: column;
    }
  }

  /* Centraliza o conteúdo do modal */
.modal-content {
  border-radius: 20px;
  padding: 2rem;
  backdrop-filter: blur(5px);
}

/* Imagem da logo */
.modal-body img {
  max-height: 80px;
  margin-bottom: 1rem;
}

/* Estiliza os campos de input */
.modal-body .form-control {
  border-radius: 30px;
  padding: 0.8rem 1.2rem;
  font-size: 1rem;
  background-color: rgba(255, 255, 255, 0.85);
}

/* Estiliza os ícones */
.input-group-text {
  border-radius: 30px 0 0 30px;
  background-color: #DBA632;
  color: white;
  border: none;
}

/* Botão de entrar */
.modal-body button.btn {
  border-radius: 30px;
  font-weight: bold;
  padding: 0.6rem 1.5rem;
  transition: all 0.3s ease;
}

.modal-body button.btn:hover {
  opacity: 0.9;
  transform: scale(1.05);
}

/* Corrige espaçamento entre campos */
.modal-body .input-group {
  margin-bottom: 1.2rem;
}

/* Link de cadastro */
.modal-body p a {
  text-decoration: none;
  font-weight: bold;
}

</style>

<nav class="navbar">
  <!-- LOGO -->
  <div class="navbar-left">
    <a href="index.php">
      <img src="image/MENTE_RENOVADA-LOGO.png" alt="Logo" />
    </a>
  </div>

  <!-- MENU CENTRAL -->
  <div class="navbar-center">
    <ul>
      <li><a class="nav-link" href="index.php">Início</a></li>
      <li><a class="nav-link" href="sessao.php">Sessões</a></li>
      <li><a class="nav-link" href="paciente.php">Pacientes</a></li>
    </ul>
  </div>

  <!-- LOGIN ou FOTO -->
  <div class="navbar-right">
    <?php if (!isset($_SESSION['login_admin'])): ?>
      <div class="nav-buttons">
        <a class="nav-link registrar-btn" data-bs-toggle="modal" data-bs-target="#modalRegistro">
          <span class="registrar-text"><i class="bi bi-person-plus-fill"></i> Registre-se</span>
        </a>
        <a class="nav-link" data-bs-toggle="modal" data-bs-target="#modalLogin">
          <span class="login-text"><i class="bi bi-person-fill"></i> Entrar</span>
        </a>
      </div>
    <?php else: ?>
      <a href="perfil_ps.php" title="Meu Perfil">
        <img src="<?php echo htmlspecialchars($fotoPerfilPath); ?>" alt="Foto de Perfil" class="perfil-img" />
      </a>
    <?php endif; ?>
  </div>
</nav>


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
    <div class="modal-content border-0" style="background: url('image/Cadastro.png') center/cover no-repeat;">
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