<!-- INDEX -->

<?php
// Inicia sessão para recuperar o psicólogo logado
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_name('Mente_Renovada');
  session_start();
}

// Recupera o ID do psicólogo se estiver logado, senão define como null
$psicologoId = isset($_SESSION['psicologo_id']) ? (int) $_SESSION['psicologo_id'] : null;

// Inicia conexão com o banco de dados (PDO)
include 'conn/conexao.php';

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
    $inicioMes = date('Y-m-01');  // primeiro dia do mês
    $fimMes    = date('Y-m-t');   // último dia do mês
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
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');

    :root {
      --brand-blue: #3387b6;
      --brand-yellow: #dc9e23;
      --brand-red: #d4435a;
      --bg-light: #f0f4f8;
      --text-dark: #3387b6;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background-color: var(--bg-light);
      color: var(--text-dark);
    }

    .jumbotron {
      background:
        url('image/Renovada.png') center center / cover no-repeat,
        linear-gradient(135deg, rgba(51, 135, 182, 0.8), rgba(212, 67, 90, 0.8));
      color: #fff;
      padding: 18rem 1.5rem;
      border-radius: .75rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      margin-bottom: 2.5rem;
      position: relative;
      overflow: hidden;
    }

    .card-custom {
      border: none;
      border-radius: 1rem;
      background: #ffffff;
      box-shadow: 8px 8px 16px rgba(0, 0, 0, 0.05),
        -8px -8px 16px rgba(255, 255, 255, 0.7);
      transition: transform .3s, box-shadow .3s;
      position: relative;
      overflow: hidden;
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

    .card-custom.border-primary,
    .card-custom.border-success,
    .card-custom.border-info,
    .card-custom.border-warning {
      border-width: 2px;
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
  </style>
</head>

<body class="d-flex flex-column min-vh-100 fundofixo">
  <!-- Menu público (navbar) -->
  <?php include 'menu_publico.php'; ?>

  <!-- Conteúdo principal -->
  <main class="flex-fill container py-5">
    <!-- Jumbotron de boas‑vindas -->
    <div class="jumbotron text-center"></div>

    <!-- Seção de resumo em cards -->
    <div class="row g-4">
      <!-- Card: Total de Pacientes -->
      <div class="col-12 col-md-6 col-lg-3">
        <a href="paciente.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Seus pacientes cadastrados">
          <div class="card border-primary h-100 shadow-sm rounded card-custom">
            <div class="card-body text-center">
              <i class="bi bi-people-fill fs-1 text-primary"></i>
              <h5 class="card-title mt-2">Pacientes</h5>
              <p class="display-6 mb-0"><?= $totalPacientes ?></p>
            </div>
          </div>
        </a>
      </div>

      <!-- Card: Total de Sessões -->
      <div class="col-12 col-md-6 col-lg-3">
        <a href="sessao.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Suas sessões agendadas com seus pacientes">
          <div class="card border-success h-100 shadow-sm rounded card-custom">
            <div class="card-body text-center">
              <i class="bi bi-journal-medical fs-1 text-success"></i>
              <h5 class="card-title mt-2">Sessões</h5>
              <p class="display-6 mb-0"><?= $totalSessoes ?></p>
            </div>
          </div>
        </a>
      </div>

      <!-- Card: Pacientes Ativos vs. Inativos -->
      <div class="col-12 col-md-6 col-lg-3">
        <a href="paciente.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Seus pacientes ativos e inativos no sistema">
          <div class="card border-info h-100 shadow-sm rounded card-custom">
            <div class="card-body text-center">
              <i class="bi bi-person-check-fill fs-1 text-info me-4"></i>
              <i class="bi bi-person-fill-x fs-1 text-danger"></i>
              <h5 class="card-title mt-2">Ativos / Inativos</h5>
              <p class="fs-4 mb-0">
                <span class="fw-bold"><?= $pacientesAtivos ?></span>
                <span class="text-muted mx-1">/</span>
                <span class="fw-light"><?= $pacientesInativos ?></span>
              </p>
            </div>
          </div>
        </a>
      </div>

      <!-- Card: Sessões este Mês -->
      <div class="col-12 col-md-6 col-lg-3">
        <a href="sessao.php" class="text-decoration-none" data-bs-toggle="tooltip" title="Sessões agendadas ou realizadas no mês atual">
          <div class="card border-warning h-100 shadow-sm rounded card-custom">
            <div class="card-body text-center">
              <i class="bi bi-calendar-check-fill fs-1 text-warning"></i>
              <h5 class="card-title mt-2">Sessões este mês</h5>
              <p class="display-6 mb-0"><?= $sessoesMes ?></p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </main>

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
