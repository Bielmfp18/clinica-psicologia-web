<!-- INDEX -->

<?php

// Definir um tratador de exceções não capturadas
set_exception_handler(function ($e) {
  http_response_code(500);
  $errorMsg = "Erro fatal: " . $e->getMessage();
  include __DIR__ . '/conn/error.php';
  exit;
});

// Definir um tratador de erros do PHP
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
  http_response_code(500);
  $errorMsg = "Erro interno: $errstr em $errfile na linha $errline";
  include __DIR__ . '/conn/error.php';
  exit;
});

// Inicia sessão para recuperar o psicólogo logado
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_name('Mente_Renovada');
  session_start();
}

// Recupera o ID do psicólogo se estiver logado, senão define como null
$psicologoId = isset($_SESSION['psicologo_id']) ? (int) $_SESSION['psicologo_id'] : null;

// Inicia conexão com o banco de dados (PDO) e inclui o init.php para tratamento de erros
include 'conn/conexao.php';
include 'conn/init.php';

// Consulta total de pacientes cadastrados
try {
  if ($psicologoId !== null) {
    $stmt = $conn->prepare("
      SELECT COUNT(*) AS total_pacientes 
      FROM paciente 
      WHERE psicologo_id = :psicologo_id
    ");
    $stmt->execute([':psicologo_id' => $psicologoId]);
    $totalPacientes = (int) $stmt->fetchColumn();
  } else {
    $totalPacientes = 0;
  }
} catch (Exception $e) {
  $totalPacientes = 0;
}

// Consulta total de sessões AGENDADAS
try {
  if ($psicologoId !== null) {
    $stmt = $conn->prepare("
      SELECT COUNT(*) AS total_sessoes 
      FROM sessao 
      WHERE psicologo_id = :psicologo_id
        AND status_sessao = 'AGENDADA'
    ");
    $stmt->execute([':psicologo_id' => $psicologoId]);
    $totalSessoes = (int) $stmt->fetchColumn();
  } else {
    $totalSessoes = 0;
  }
} catch (Exception $e) {
  $totalSessoes = 0;
}

// Pacientes ativos vs. inativos
try {
  if ($psicologoId !== null) {
    $stmt = $conn->prepare("
        SELECT 
          SUM(CASE WHEN ativo=1 THEN 1 ELSE 0 END) AS ativos,
          SUM(CASE WHEN ativo=0 THEN 1 ELSE 0 END) AS inativos
        FROM paciente
        WHERE psicologo_id = :psicologo_id
    ");
    $stmt->execute([':psicologo_id' => $psicologoId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pacientesAtivos   = (int) $row['ativos'];
    $pacientesInativos = (int) $row['inativos'];
  } else {
    $pacientesAtivos = $pacientesInativos = 0;
  }
} catch (Exception $e) {
  $pacientesAtivos = $pacientesInativos = 0;
}

// Sessões AGENDADAS do mês corrente
try {
  if ($psicologoId !== null) {
    $inicioMes = date('Y-m-01 00:00:00');  // primeiro dia do mês
    $fimMes    = date('Y-m-t 23:59:59');   // último dia do mês
    $stmt = $conn->prepare(
      "SELECT COUNT(*) AS sessoes_mes 
       FROM sessao 
       WHERE psicologo_id = :psicologo_id
         AND data_hora_sessao BETWEEN :inicio AND :fim
         AND status_sessao = 'AGENDADA'"
    );
    $stmt->execute([
      ':psicologo_id' => $psicologoId,
      ':inicio'       => $inicioMes,
      ':fim'          => $fimMes
    ]);
    $sessoesMes = (int) $stmt->fetchColumn();
  } else {
    $sessoesMes = 0;
  }
} catch (Exception $e) {
  $sessoesMes = 0;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mente Renovada</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!-- Bootstrap 5 JS (bundle já inclui Popper.js) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- jQuery  -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Link para o ícone da aba -->
  <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');

    :root {
      --brand-blue: #3387b6;
      --brand-yellow: #dc9e23;
      --brand-red: #d4435a;
      --bg-light: #f0f4f8;
      --text-dark: #3387b6;
    }

    body.fundofixo {
      background-color: var(--bg-light);
      color: var(--text-dark);
      padding-top: 90px;
      z-index: 1;
    }


    main {
      min-height: calc(100vh - 200px);
      /* Ajuste o valor conforme altura da navbar + rodapé */
    }


    .jumbotron {
      background:
        url('image/Principal.png') center center / cover no-repeat,
        linear-gradient(135deg, rgba(51, 135, 182, 0.8), rgba(212, 67, 90, 0.8));
      color: #fff;
      padding: 6rem;
      border-radius: .75rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
      position: relative;
      overflow: hidden;
      width: 60%;
      margin-left: auto;
      margin-right: auto;
    }

    .card-custom {
      box-sizing: border-box;
      width: clamp(140px, 100%, 260px);
      aspect-ratio: 4 / 3;
      border-radius: 1rem;
      background: #ffffff;
      box-shadow: 8px 8px 16px rgba(0, 0, 0, 0.05), -8px -8px 16px rgba(255, 255, 255, 0.7);
      transition: transform .3s, box-shadow .3s;
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      transform-origin: center center;
    }

    .card-custom::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg,
          rgba(255, 255, 255, 0.2),
          rgba(0, 0, 0, 0.05));
      pointer-events: none;
    }

    .card-custom:hover {
      transform: translateY(-5px);
      box-shadow: 12px 12px 24px rgba(0, 0, 0, 0.08),
        -12px -12px 24px rgba(255, 255, 255, 0.8);
    }

    .card-custom .card-body i {
      font-size: 2.5rem;
      margin-bottom: .5rem;
    }

    .card-custom .display-6 {
      font-weight: 700;
    }

    .container {
      max-width: 1200px;
    }

    .row.g-4 {
      margin-top: 2rem;
    }

    .row.g-4 .col-12 {
      display: flex;
    }

    .row.g-4 .col-12 a {
      flex: 1;
    }


    /* corpo do card ocupa o espaço inteiro e centraliza conteúdo */
    .card-custom .card-body {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: .35rem;
    }

    /* ícones e textos escalam proporcionalmente com clamp */
    .card-custom .card-body i {
      font-size: clamp(1.2rem, 4vw, 2.6rem);
      margin-bottom: .35rem;
    }

    .card .bi-person-check-fill {
      display: flex;
      justify-items: center;
    }

    .card-custom .display-6 {
      font-weight: 700;
      font-size: clamp(1rem, 3vw, 1.9rem);
    }

    /* borda colorida */
    .card-custom.border-primary,
    .card-custom.border-success,
    .card-custom.border-info,
    .card-custom.border-warning {
      border-width: 2px;
      border-style: solid;
    }

    /* Rodapé (Distância) */
    .footer-spacer {
      height: 120px;
      width: 100%;
      display: block;
    }

    @media (max-width: 991.98px) {
      .card-custom {
        padding: 0.4rem;
        overflow: hidden;
        height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
      }

      .d-flex,
      .flex-column {
        padding-left: 0.5rem;
        padding-right: 1rem;
      }

      .card-custom .card-body {
        padding: 0.5rem !important;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
      }

      .card-custom .card-body i {
        font-size: 1.4rem;
        margin-bottom: 0.2rem;
      }

      .card-custom .card-title {
        font-size: 0.9rem;
        margin: 0.2rem 0;
        text-align: center;
      }

      .card-custom .display-6,
      .card-custom .fs-2 {
        font-size: 1rem;
        margin: 0;
      }

      .card-custom .d-flex {
        gap: 0.4rem;
        flex-wrap: nowrap;
        justify-content: center;
      }
    }
  </style>
</head>

<body class="d-flex flex-column min-vh-100 fundofixo">
  <!-- Menu público (navbar) -->
  <?php include 'menu_publico.php'; ?>

  <!-- Conteúdo principal -->
  <main class="flex-fill container py-5">
    <!-- Jumbotron de boas‑vindas -->
    <div class="jumbotron text-center"></div>

    <?php if (!isset($_SESSION['psicologo_id'])): ?>
      <div class="text-center mt-4">
        <p class="lead">Cadastre-se ou faça login para acessar sua área de trabalho.</p>
      </div>
    <?php endif; ?>


    <?php if (isset($_SESSION['psicologo_id'])): ?>
      <!-- Seção de resumo em cards -->
      <div class="row g-4">
        <!-- Card: Total de Pacientes -->
        <div class="col-6 col-md-6 col-lg-3">
          <a href="paciente.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Seus pacientes cadastrados">
            <div class="card border-primary h-100 shadow-sm rounded card-custom">
              <div class="card-body text-center">
                <i class="bi bi-people-fill fs-1 text-primary"></i>
                <h5 class="card-title mt-2">Pacientes</h5>
                <p class="display-6 mb-0 text-primary"><?= $totalPacientes ?></p>
              </div>
            </div>
          </a>
        </div>

        <!-- Card: Pacientes Ativos vs. Inativos -->
        <div class="col-6 col-md-6 col-lg-3">
          <a href="paciente.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Seus pacientes ativos e inativos no sistema">
            <div class="card border-info h-100 shadow-sm rounded card-custom">
              <div class="card-body text-center">
                <!-- Ícones lado a lado -->
                <div class="d-flex justify-content-center align-items-center gap-3 mb-1">
                  <i class="bi bi-person-check-fill fs-1 text-info"></i>
                  <i class="bi bi-person-fill-x fs-1 text-danger"></i>
                </div>

                <!-- Título -->
                <h5 class="card-title">Ativos / Inativos</h5>

                <!-- Números -->
                <p class="display-6 mb-0">
                  <span class="fw-bold text-info"><?= $pacientesAtivos ?></span>
                  <span class="text-muted mx-1">/</span>
                  <span class="fw-bold text-danger"><?= $pacientesInativos ?></span>
                </p>
              </div>
            </div>
          </a>
        </div>

        <!-- Card: Total de Sessões -->
        <div class="col-6 col-md-6 col-lg-3">
          <a href="sessao.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Suas sessões agendadas com seus pacientes">
            <div class="card border-success h-100 shadow-sm rounded card-custom">
              <div class="card-body text-center">
                <i class="bi bi-journal-medical fs-1 text-dark"></i>
                <h5 class="card-title mt-2">Sessões</h5>
                <p class="display-6 mb-0 text-dark"><?= $totalSessoes ?></p>
              </div>
            </div>
          </a>
        </div>

        <!-- Card: Sessões este Mês -->
        <div class="col-6 col-md-6 col-lg-3">
          <a href="sessao.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Sessões agendadas no mês atual">
            <div class="card border-warning h-100 shadow-sm rounded card-custom">
              <div class="card-body text-center">
                <i class="bi bi-calendar-check-fill fs-1 text-warning"></i>
                <h5 class="card-title mt-2">Sessões este mês</h5>
                <p class="display-6 mb-0 text-warning"><?= $sessoesMes ?></p>
              </div>
            </div>
          </a>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <!-- Spacer para garantir espaço antes do footer -->
  <div class="footer-spacer" aria-hidden="true"></div>

  <!-- Rodapé -->
  <?php include 'rodape.php'; ?>


  <script>
    // Inicializa tooltips do Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(el) {
      new bootstrap.Tooltip(el);
    });
  </script>
</body>

</html> 