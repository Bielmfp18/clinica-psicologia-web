<?php
// SESSÃO ATUALIZA

// Exibe erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
  $_SESSION['flash'] = [
    'type'    => 'warning',
    'message' => 'Faça login antes de atualizar sessões.'
  ];
  header('Location: index.php');
  exit;
}

// Resgata e apaga o flash (para exibir se vier de um redirect)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Inicia conexão com o banco de dados (PDO) e inclui o init.php para tratamento de erros
include 'conn/conexao.php';
include 'conn/init.php';
// Inclui a função de histórico
include 'funcao_historico.php';

$id_psicologo = (int) $_SESSION['psicologo_id'];

// Verifica se o ID da sessão foi informado via GET
if (!isset($_GET['id'])) {
  die('ID da sessão não informado.');
}
$id = (int) $_GET['id'];


// Busca dados da sessão existente
try {
  $sql     = "SELECT * FROM sessao WHERE id = :id AND psicologo_id = :psid";
  $stmt    = $conn->prepare($sql);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->bindParam(':psid', $id_psicologo, PDO::PARAM_INT);
  $stmt->execute();
  $sessao = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$sessao) {
    die('Sessão não encontrada.');
  }
} catch (PDOException $e) {
  die('Erro ao buscar sessão: ' . $e->getMessage());
}

// Carrega lista de pacientes ativos para seleção
$sql_pac = $conn->prepare("
  SELECT id, nome
    FROM paciente
   WHERE psicologo_id = :psid
     AND ativo = 1
ORDER BY nome
");
$sql_pac->bindParam(':psid', $id_psicologo, PDO::PARAM_INT);
$sql_pac->execute();
$pacientes = $sql_pac->fetchAll(PDO::FETCH_ASSOC);


// Se veio via POST, processa atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Recebe dados do formulário
  $paciente_id      = (int) ($_POST['paciente_id'] ?? 0);
  $anotacoes        = $_POST['anotacoes'] ?? '';
  $data_hora        = $_POST['data_hora_sessao'] ?? '';
  $data_atualizacao = date('Y-m-d H:i:s');
  $status           = $_POST['status_sessao'] ?? $sessao['status_sessao'];

  try {
    // 1) Chama procedure que atualiza a sessão
    $sql = "CALL ps_sessao_update(
              :psid,
              :pspsicologo_id,
              :pspaciente_id,
              :psanotacoes,
              :psdata_hora,
              :psdata_atualizacao,
              :psstatus
           )";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':psid',               $id,               PDO::PARAM_INT);
    $stmt->bindParam(':pspsicologo_id',     $id_psicologo,     PDO::PARAM_INT);
    $stmt->bindParam(':pspaciente_id',      $paciente_id,      PDO::PARAM_INT);
    $stmt->bindParam(':psanotacoes',        $anotacoes,        PDO::PARAM_STR);
    $stmt->bindParam(':psdata_hora',        $data_hora,        PDO::PARAM_STR);
    $stmt->bindParam(':psdata_atualizacao', $data_atualizacao, PDO::PARAM_STR);
    $stmt->bindParam(':psstatus',           $status,           PDO::PARAM_STR);

    if ($stmt->execute()) {
      // 2) Fecha o cursor da procedure para liberar o PDO
      $stmt->closeCursor();

      // 3) Recupera nome do paciente para histórico
      $stmtInfo = $conn->prepare("
        SELECT p.nome AS nomePaciente
          FROM sessao s
          JOIN paciente p ON s.paciente_id = p.id
         WHERE s.id = :id
      ");
      $stmtInfo->bindValue(':id', $id, PDO::PARAM_INT);
      $stmtInfo->execute();
      $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
      $nomePaciente = $info['nomePaciente'] ?? "(ID {$id})";
      $stmtInfo->closeCursor();

      // 4) Registra no histórico
      registrarHistorico(
        $conn,
        $id_psicologo,
        'Atualização',
        'Sessão',
        "Sessão de {$nomePaciente} atualizada"
      );

      // 5) Prepara flash de sucesso e redireciona para a lista
      $_SESSION['flash'] = [
        'type'    => 'warning',
        'message' => 'Sessão atualizada com sucesso!'
      ];
      header('Location: sessao.php');
      exit;
    }

    // Se não executou, define flash de erro e volta ao formulário
    $_SESSION['flash'] = [
      'type'    => 'danger',
      'message' => 'Erro ao tentar atualizar a sessão!'
    ];
    header("Location: sessao_atualiza.php?id={$id}");
    exit;
  } catch (PDOException $e) {
    // Flash de erro e redireciona de volta ao formulário
    $_SESSION['flash'] = [
      'type'    => 'danger',
      'message' => 'Erro ao atualizar sessão: ' . addslashes($e->getMessage())
    ];
    header("Location: sessao_atualiza.php?id={$id}");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Atualizar Sessão</title>
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
      padding-top: 30px;
      z-index: 1;
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

  <!-- Exibe flash se vier de redirect -->
  <?php if (!empty($flash)): ?>
    <div class="alert-wrapper position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1050;">
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  <?php endif; ?>

  <?php include 'menu_publico.php'; ?>

  <main class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="col-12 col-sm-10 col-md-6 col-lg-5">
      <div class="position-relative mb-4">
        <a href="sessao.php" class="btn btn-voltar position-absolute start-0 top-50 translate-middle-y">
          <i class="bi bi-arrow-left text-white"></i>
        </a>
        <h2 class="text-white fw-bold p-2 rounded text-center" style="background-color:#DBA632;">
          Atualizar Sessão
        </h2>
      </div>
      <div class="card p-4">
        <form method="POST">
          <input type="hidden" name="id" value="<?= $sessao['id'] ?>">
          <input type="hidden" name="psicologo_id" value="<?= $id_psicologo ?>">
          <input type="hidden" name="status_sessao" value="<?= $sessao['status_sessao'] ?>">

          <div class="mb-4">
            <label for="paciente_id" class="form-label">Paciente:</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <select name="paciente_id" id="paciente_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($pacientes as $p): ?>
                  <option value="<?= $p['id'] ?>"
                    <?= $p['id'] == $sessao['paciente_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label for="data_hora_sessao" class="form-label">Data e Hora:</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-clock-fill"></i></span>
              <input type="datetime-local" name="data_hora_sessao" id="data_hora_sessao"
                class="form-control"
                value="<?= date('Y-m-d\\TH:i', strtotime($sessao['data_hora_sessao'])) ?>"
                required>
            </div>
          </div>

          <div class="mb-4">
            <label for="anotacoes" class="form-label">Anotações:</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
              <textarea name="anotacoes" id="anotacoes" class="form-control"
                placeholder="Anotações pós-sessão"><?= htmlspecialchars($sessao['anotacoes']) ?></textarea>
            </div>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn text-white">
              <i class="bi bi-save-fill me-2 text-white"></i> Atualizar Sessão
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const ta = document.getElementById('anotacoes');

      function ajusta() {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
      }
      ajusta();
      ta.addEventListener('input', ajusta);
    });
  </script>
</body>

</html>