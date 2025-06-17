<!-- menu_publico.php -->
<nav class="navbar navbar-expand-lg">
  <a href="../index.php" class="navbar-brand ms-3">
    <img src="image/MENTE_RENOVADA-LOGO.png" alt="Logotipo" />
  </a>

  <div class="container-fluid d-flex justify-content-between align-items-center px-4">
    <div class="collapse navbar-collapse mx-auto justify-content-center" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item me-3"><a class="nav-link active" href="#">Início</a></li>
        <li class="nav-item me-3"><a class="nav-link" href="#">Sessões</a></li>
        <li class="nav-item me-3"><a class="nav-link" href="#">Pacientes</a></li>
      </ul>
      <a href="perfil.php" class="nav-link">
        <i class="bi bi-person-circle perfil-icon"></i>
      </a>
    </div>
  </div>
</nav>

<style>
  html, body {
    margin: 0;
    padding: 0;
  }

  body {
    padding-top: 90px; /* Compensa altura do menu fixo */
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
    background-color: rgba(255, 255, 255, 0.8); /* Fundo branco semitransparente */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Sombra sutil */
    z-index: 1000;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }

  .navbar.scrolled {
    background-color: rgba(255, 255, 255, 0.95); /* Mais opaco ao rolar */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .navbar-brand {
    display: flex;
    align-items: center;
  }

  .navbar-brand img {
    height: 100px !important;
    width: 250px !important;
    object-fit: contain;
    margin-left: 80px;
    margin-bottom: 1px;
    transition: transform 0.3s ease-in-out, filter 0.3s ease-in-out;
  }

  .navbar-brand img:hover {
    transform: scale(1.1);
    filter: drop-shadow(0 0 10px #DBA632);
  }

  .navbar-nav {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    list-style: none;
    margin: 0 75px;
    padding: 0;
  }

  .navbar-nav .nav-item {
    margin: 0 1rem;
  }

  .nav-link {
    font-weight: bold;
    padding: 0.5rem 0;
    line-height: 1;
    color: #333 !important; /* Cor escura para contraste */
    transition: color 0.3s;
    text-decoration: none;
  }

  .nav-link.active,
  .nav-link:hover {
    color: #DBA632 !important;
  }

  .perfil-icon {
    font-size: 2.6rem;
    color: #DBA632;
    transition: color 0.3s;
    margin-bottom: 20px;
  }

  .perfil-icon:hover {
    color: #b7861e;
  }
</style>

<!-- Script para mudar opacidade ao rolar -->
<script>
  window.addEventListener('scroll', function () {
    const nav = document.querySelector('.navbar');
    nav.classList.toggle('scrolled', window.scrollY > 50);
  });
</script>
