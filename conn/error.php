<?php
// ============================
// ERROR HANDLER
// ============================
$errorMsg = "Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.";
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Error</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS e ícones -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Link para o ícone da aba -->
  <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">

  <style>
    /* RESET E BODY */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f8f9fa;
      color: #333;
    }

    /* FULL‑PAGE ERROR */
    .page-error {
      position: relative;
      width: 100%;
      max-width: 800px;
      padding: 2rem;
      text-align: center;
    }

    .page-error .error-logo {
      width: 80px;
      margin-bottom: 1.5rem;
    }

    .page-error h1 {
      font-size: 3rem;
      font-weight: bold;
      color: #dc3545;
      margin-bottom: 0.5rem;
    }

    .page-error h2 {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: #495057;
    }

    .page-error p {
      font-size: 1.1rem;
      color: #6c757d;
      margin-bottom: 2rem;
      white-space: pre-line;
    }

    /* BOTÃO VOLTAR SIMPLES */
    .btn-back {
      position: absolute;
      top: 1rem;
      left: 1rem;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #dc3545;
      cursor: pointer;
      transition: color .2s;
    }

    .btn-back:hover {
      color: #a71d2a;
    }

    /* AÇÕES */
    .page-error .btn {
      margin: 0 .5rem;
      padding: .75rem 1.5rem;
      border-radius: .5rem;
      font-size: 1rem;
      transition: transform .2s, box-shadow .2s;
    }

    .page-error .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 576px) {
      .page-error h1 {
        font-size: 4rem;
      }

      .page-error h2 {
        font-size: 1.5rem;
      }

      .page-error p {
        font-size: 1rem;
      }

      .page-error .error-logo {
        width: 60px;
        margin-bottom: 1rem;
      }
    }
  </style>
</head>

<body>


  <div class="page-error">

    <!-- Imagem da página de erro -->
    <img src="image/MENTE_RENOVADA-LOGO-removebg-preview-removebg-preview.png" alt="Logo-Mente-Renovada" style="height: 10rem;">

    <!-- Código de erro e mensagens -->
    <h1>ERROR 500</h1>
    <strong><p><?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?></p></strong>

    <!-- Botões de ação -->
    <a href="index.php" class="btn btn-danger">
      <i class="bi bi-house-door-fill"></i> Início
    </a>
    <a href="javascript:history.back()" class="btn btn-warning">
      <i class="bi bi-arrow-left-circle-fill"></i> Voltar à página anterior
    </a>

  </div>

  <!-- Bootstrap JS opcional -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>