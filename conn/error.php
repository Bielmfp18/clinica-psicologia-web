<?php
// ============================
// ERROR.php
// ============================

// Código de status que veio do handler (ou 500)
$errorCode = $GLOBALS['errorCode'] ?? http_response_code() ?? 500;

// Mensagem obrigatoriamente vinda do handler/exception.
// Se por acaso não existir, cai num texto genérico simples.
$errorMsg  = $GLOBALS['errorMsg']  ?? "Ocorreu um erro. Por favor, tente novamente mais tarde.";

// Flag para exibir ou não os botões de menu e voltar
$showMenuAndBack = $GLOBALS['showMenuAndBack'] ?? true;

// Envia o status HTTP
http_response_code($errorCode);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Error <?= htmlspecialchars($errorCode) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS e ícones -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f8f9fa;
      color: #333;
    }
    .page-error {
      width: 100%;
      max-width: 800px;
      padding: 2rem;
      text-align: center;
    }
    .page-error h1 {
      font-size: 3rem;
      font-weight: bold;
      color: #dc3545;
      margin-bottom: .5rem;
    }
    .page-error p {
      font-size: 1.1rem;
      color: #6c757d;
      margin-bottom: 2rem;
      white-space: pre-line;
    }
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
  </style>
</head>

<body>
  <div class="page-error">

    <!-- Logo -->
    <img src="image/MENTE_RENOVADA-LOGO-removebg-preview-removebg-preview.png"
      alt="Logo Mente Renovada" style="height:10rem; margin-bottom:1rem;">

    <!-- Título com o código -->
    <h1>ERROR <?= htmlspecialchars($errorCode) ?></h1>

    <!-- Mensagem genérica, SEMPRE vinda do handler -->
    <p><strong><?= nl2br(htmlspecialchars($errorMsg)) ?></strong></p>

    <!-- Botões de navegação -->
    <?php if ($showMenuAndBack): ?>
      <a href="index.php" class="btn btn-danger">
        <i class="bi bi-house-door-fill"></i> Início
      </a>
      <a href="javascript:history.back()" class="btn btn-warning">
        <i class="bi bi-arrow-left-circle-fill"></i> Voltar à página anterior
      </a>
    <?php endif; ?>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
