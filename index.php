

<!-- index.php -->
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página Inicial</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!-- Bootstrap 5 JS (bundle já inclui Popper.js) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- jQuery  -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    .fundofixo {
      background: url('image/MENTE_RENOVADA.png') no-repeat center center fixed;
      background-size: cover;
      background-attachment: fixed;
      background-position: center;
    }

    main.container {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      margin-top: 30px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>

<body class="fundofixo">
  <?php include "menu_publico.php" ?>
  <main class="container">
    <h1>Bem-vindo à Mente Renovada</h1>
    <p>Esta é a página inicial do sistema.</p>
  </main>
</body>

</html>
