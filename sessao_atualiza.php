<!-- SESSÃO ATUALIZA -->

<?php
// Exibe erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
    die("<script>
        alert('Faça login antes de atualizar sessões.');
        window.location.href = 'index.php';
        </script>");
    exit;
}

include 'conn/conexao.php'; // Conexão com o banco

// Verifica se o ID da sessão foi informado via GET
if (!isset($_GET['id'])) {
    die('ID da sessão não informado.');
}
$id = (int) $_GET['id'];
$id_psicologo = (int) $_SESSION['psicologo_id'];

// Busca dados da sessão
try {
    $sql = "SELECT * FROM sessao WHERE id = :id AND psicologo_id = :psid";
    $stmt = $conn->prepare($sql);
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

// Carrega lista de pacientes ativos
$sql_pac = $conn->prepare(
    "SELECT id, nome FROM paciente WHERE psicologo_id = :psid AND ativo = 1 ORDER BY nome"
);
$sql_pac->bindParam(':psid', $id_psicologo, PDO::PARAM_INT);
$sql_pac->execute();
$pacientes = $sql_pac->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe dados do formulário
    $paciente_id = (int) $_POST['paciente_id'];
    $data_hora   = $_POST['data_hora_sessao'];
    $anotacoes   = $_POST['anotacoes'] ?? '';
    $status      = (int) $sessao['status_sessao'];

    try {
        $sql = "CALL ps_sessao_update(
                    :psid,
                    :pspsicologo_id,
                    :pspaciente_id,
                    :psanotacoes,
                    :psdata_hora,
                    :psstatus
                 )";
        $upd = $conn->prepare($sql);
        $upd->bindParam(':psid', $id, PDO::PARAM_INT);
        $upd->bindParam(':pspsicologo_id', $id_psicologo, PDO::PARAM_INT);
        $upd->bindParam(':pspaciente_id', $paciente_id, PDO::PARAM_INT);
        $upd->bindParam(':psanotacoes', $anotacoes, PDO::PARAM_STR);
        $upd->bindParam(':psdata_hora', $data_hora, PDO::PARAM_STR);
        $upd->bindParam(':psstatus', $status, PDO::PARAM_INT);

        if ($upd->execute()) {
            echo "<script>
                    alert('Sessão atualizada com sucesso!');
                    window.location.href = 'sessao.php';
                  </script>";
            exit;
        } else {
            echo "<script>alert('Erro ao atualizar sessão.'); window.history.back();</script>";
        }
    } catch (PDOException $e) {
        echo "<script>
        alert('Erro: " . addslashes($e->getMessage()) . "'); window.history.back();
        </script>";
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
    <style>
        body.fundofixo {
             background: url('image/MENTE_RENOVADA.png') no-repeat center center fixed; background-size: cover; }
        .card { background-color: rgba(255,255,255,0.92); border: none; box-shadow: 0 0 20px rgba(0,0,0,0.15); }
        .form-label { font-weight:600; }
        .input-group-text { background-color:#DBA632;color:white;border:none; }
        .btn, .btn-voltar { background-color:#DBA632;color:white;border:none; transition:0.3s ease; }
        .btn:hover, .btn-voltar:hover { background-color:#b38121!important; transform:scale(1.05); }
        textarea#anotacoes { resize:vertical; overflow:hidden; min-height:100px; max-height:400px; }
    </style>
</head>
<body class="fundofixo">
<?php include 'menu_publico.php'; ?>
<main class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="col-12 col-sm-10 col-md-6 col-lg-5">
    <div class="position-relative mb-4">
      <a href="sessao.php" class="btn btn-voltar position-absolute start-0 top-50 translate-middle-y">
        <i class="bi bi-arrow-left text-white"></i>
      </a>
      <h2 class="text-white fw-bold p-2 rounded text-center" style="background-color:#DBA632;">Atualizar Sessão</h2>
    </div>
    <div class="card p-4">
      <form method="POST" id="form_atualiza_sessao">
        <input type="hidden" name="id" value="<?= $sessao['id'] ?>">
        <input type="hidden" name="psicologo_id" value="<?= $id_psicologo ?>">

        <div class="mb-4">
          <label for="paciente_id" class="form-label">Paciente:</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <select name="paciente_id" id="paciente_id" class="form-select" required>
              <option value="">Selecione...</option>
              <?php foreach($pacientes as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id']==$sessao['paciente_id']?'selected':'' ?>><?= htmlspecialchars($p['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mb-4">
          <label for="data_hora_sessao" class="form-label">Data e Hora:</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-clock-fill"></i></span>
            <input type="datetime-local" name="data_hora_sessao" id="data_hora_sessao" class="form-control" value="<?= date('Y-m-d\\TH:i', strtotime($sessao['data_hora_sessao'])) ?>" required>
          </div>
        </div>

        <div class="mb-4">
          <label for="anotacoes" class="form-label">Anotações:</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
            <textarea name="anotacoes" id="anotacoes" class="form-control" placeholder="Observações da sessão"><?= htmlspecialchars($sessao['anotacoes']) ?></textarea>
          </div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn text-white"><i class="bi bi-save-fill me-2 text-white"></i> Atualizar Sessão</button>
        </div>
      </form>
    </div>
  </div>
</main>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const ta = document.getElementById('anotacoes');
    function ajusta(){ ta.style.height='auto'; ta.style.height=ta.scrollHeight+'px'; }
    ajusta(); ta.addEventListener('input', ajusta);
  });
</script>
</body>
</html>