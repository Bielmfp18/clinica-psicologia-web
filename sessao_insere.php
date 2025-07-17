<?php
// SESSÃO INSERE

// Exibe erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Se não estiver logado, flash e redireciona ao login
if (!isset($_SESSION['psicologo_id'])) {
  $_SESSION['flash'] = [
    'type'    => 'warning',
    'message' => 'Faça login antes de cadastrar sessões.'
  ];
  header('Location: index.php');
  exit;
}

include 'conn/conexao.php';       // Conexão com o banco de dados
include 'funcao_historico.php';   // Define registrarHistorico()

$id_psicologo = (int) $_SESSION['psicologo_id'];

// Busca lista de pacientes para o select
$sql_pacientes = $conn->prepare("
    SELECT id, nome
      FROM paciente
     WHERE psicologo_id = :psid
       AND ativo = 1
  ORDER BY nome
");
$sql_pacientes->bindParam(':psid', $id_psicologo, PDO::PARAM_INT);
$sql_pacientes->execute();
$lista_pacientes = $sql_pacientes->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Valida ID do psicólogo
  $psicologo_id = (int) ($_POST['psicologo_id'] ?? 0);
  if ($psicologo_id !== $id_psicologo) {
    die('ID do psicólogo inválido.');
  }

  // Dados da sessão
  $paciente_id      = (int) ($_POST['paciente_id'] ?? 0);
  $data_hora        = $_POST['data_hora_sessao'] ?? '';
  $data_atualizacao = date('Y-m-d H:i:s');
  $anotacoes        = $_POST['anotacoes'] ?? '';
  $status           = 1;

  // Busca o nome do paciente para o histórico
  $stmtNome = $conn->prepare("SELECT nome FROM paciente WHERE id = :pid");
  $stmtNome->bindParam(':pid', $paciente_id, PDO::PARAM_INT);
  $stmtNome->execute();
  $nomePaciente = $stmtNome->fetchColumn() ?: 'Paciente';
  $stmtNome->closeCursor();

  try {
    // Insere a sessão via procedure
    $sql = "CALL ps_sessao_insert(
                  :pspsicologo_id,
                  :pspaciente_id,
                  :psanotacoes,
                  :psdata_hora,
                  :psdata_atualizacao,
                  :psstatus
                )";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pspsicologo_id', $psicologo_id);
    $stmt->bindParam(':pspaciente_id',   $paciente_id);
    $stmt->bindParam(':psanotacoes',     $anotacoes);
    $stmt->bindParam(':psdata_hora',     $data_hora);
    $stmt->bindParam(':psdata_atualizacao', $data_atualizacao);
    $stmt->bindParam(':psstatus',        $status);

    if ($stmt->execute()) {
      // Fecha cursor para evitar pending result sets
      $stmt->closeCursor();

      // Registra no histórico
      registrarHistorico(
        $conn,
        $psicologo_id,
        'Adição',
        'Sessão',
        "Sessão de {$nomePaciente} adicionada"
      );

      // Prepara flash e redireciona para lista de sessões
      $_SESSION['flash'] = [
        'type'    => 'success',
        'message' => 'Sessão adicionada com sucesso!'
      ];
      header('Location: sessao.php');
      exit;
    } else {
      throw new Exception('Erro ao tentar adicionar a sessão!');
    }
  } catch (Exception $e) {
    $_SESSION['flash'] = [
      'type'    => 'danger',
      'message' => 'Erro: ' . $e->getMessage()
    ];
    header('Location: sessao.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Adicionar Sessão</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Link para o ícone da aba -->
  <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">
  <style>
    body.fundofixo {
      background: url('image/MENTE_RENOVADA.png') no-repeat center center fixed;
      background-size: cover;
    }

    .card {
      background-color: rgba(255, 255, 255, 0.92);
      border: none;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .form-label {
      font-weight: 600;
    }

    .input-group-text {
      background-color: #DBA632;
      color: white;
      border: none;
    }

    .btn,
    .btn-voltar {
      background-color: #DBA632;
      color: white;
      border: none;
      transition: background-color .3s ease, transform .2s ease;
    }

    .btn:hover,
    .btn-voltar:hover {
      background-color: #b38121 !important;
      transform: scale(1.05);
    }
  </style>
</head>

<body class="fundofixo">

  <?php include 'menu_publico.php'; ?>

  <main class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-12 col-sm-10 col-md-6 col-lg-5">

      <!-- Cabeçalho com botão e título -->
      <div class="position-relative mb-4">
        <a href="sessao.php" class="btn btn-voltar position-absolute start-0 top-50 translate-middle-y">
          <i class="bi bi-arrow-left text-white"></i>
        </a>
        <h2 class="text-white fw-bold p-2 rounded text-center" style="background-color:#DBA632;">
          Adicionar Sessão
        </h2>
      </div>

      <div class="card p-4">
        <form method="POST" id="form_insere_sessao">
          <!-- Campo oculto para enviar o ID do psicólogo -->
          <input type="hidden" name="psicologo_id" value="<?= $id_psicologo ?>">

          <!-- Paciente -->
          <div class="mb-4">
            <label for="paciente_id" class="form-label">Paciente:</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <select name="paciente_id" id="paciente_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($lista_pacientes as $p): ?>
                  <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Data e Hora da Sessão -->
          <div class="mb-4">
            <label for="data_hora_sessao" class="form-label">Data e Hora:</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-clock-fill"></i></span>
              <input type="datetime-local" name="data_hora_sessao" id="data_hora_sessao"
                class="form-control" min="" required>
            </div>
          </div>

          <!-- Anotações -->
          <div class="mb-4">
            <label for="anotacoes" class="form-label">Anotações:</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
              <textarea name="anotacoes" id="anotacoes" class="form-control"
                placeholder="Anotações pré-sessão"></textarea>
            </div>
          </div>

          <!-- Botão -->
          <div class="d-grid">
            <button type="submit" class="btn text-white">
              <i class="bi bi-plus-square me-2 text-white"></i> Adicionar Nova Sessão
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Ajusta textarea
      const ta = document.getElementById('anotacoes');

      function ajustaAltura() {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
      }
      ajustaAltura();
      ta.addEventListener('input', ajustaAltura);

      // Preenche e limita datetime-local
      const input = document.getElementById('data_hora_sessao');
      const now = new Date(),
        pad = n => n.toString().padStart(2, '0');
      const hoje = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
      input.min = hoje;
      input.value = hoje;
    });
  </script>
</body>

</html>