<?php
// Página de erro personalizada para o sistema Mente Renovada

// Se a mensagem de erro não estiver definida, define um padrão
if (!isset($errorMsg)) {
  $errorMsg = "Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Erro no Sistema</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS e ícones -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    /* Zera margens e paddings globais */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    /* Centraliza vertical e horizontalmente */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f6f9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 20px;
    }

    /* Container da mensagem de erro */
    .error-box {
      position: relative;            /* referência para o botão absoluto */
      background: #ffffff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
      max-width: 600px;
      width: 100%;
      text-align: center;
      border-top: 8px solid #e74c3c;
      animation: slideIn 0.4s ease;
    }

    @keyframes slideIn {
      from {
        transform: translateY(-20px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    /* Logo acima do título */
    .error-box img {
      width: 120px;
      margin-bottom: 20px;
    }

    /* Título da caixa de erro */
    .error-box h1 {
      color: #e74c3c;
      font-size: 26px;
      margin-bottom: 12px;
    }

    /* Texto de descrição do erro */
    .error-box p {
      color: #333;
      font-size: 16px;
      margin-bottom: 25px;
      white-space: pre-line;
      word-break: break-word;
    }

    /* Botão de voltar posicionado no canto superior esquerdo */
    .btn-back {
      position: absolute;            /* tira do fluxo e posiciona em relação à .error-box */
      top: 16px;                     /* distância do topo da .error-box */
      left: 16px;                    /* distância da esquerda da .error-box */
      padding: 8px;                  /* tamanho do clique confortável */
      font-size: 1.2rem;             /* aumenta o ícone */
      line-height: 1;                /* corrige alinhamento de ícone */
    }

    @media (max-width: 480px) {
      .error-box {
        padding: 30px 20px;
      }
      .error-box h1 {
        font-size: 22px;
      }
      .btn-back {
        top: 12px;
        left: 12px;
        padding: 6px;
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>

   <!-- Menu público -->
    <?php include 'menu_publico.php'; ?>

  <div class="error-box">
    <!-- Botão vermelho de voltar, usando classes Bootstrap e ícone -->
  

    <!-- Logotipo do sistema -->
    <img src="image/logo_principal.png" alt="Logo Mente Renovada">

    <!-- Título de erro -->
    <h1>Ocorreu um erro</h1>
    <br>

    <!-- Mensagem detalhada do erro (escapando HTML para segurança) -->
    <strong><?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?></strong>
  </div>
</body>
</html>
