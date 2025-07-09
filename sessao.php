<?php
// SESSÃO

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
  die("<script>
        alert('Faça login antes de acessar as sessões.');
        window.location.href = 'index.php';
      </script>");
  exit;
}

// Pega o ID do psicólogo logado
$id_psico = (int) $_SESSION['psicologo_id'];

// Arquivo de conexão com o banco de dados
include 'conn/conexao.php';

// Filtro por status da sessão
$status_sessao = isset($_GET['status_sessao']) ? trim($_GET['status_sessao']) : '';

// Monta SQL usando subquery para trazer o nome do paciente
if (in_array($status_sessao, ['AGENDADA', 'REALIZADA', 'CANCELADA'])) {
  // --- SOMENTE MINHAS SESSÕES COM O STATUS SELECIONADO ---
  $sql = "
    SELECT s.*, (SELECT p.nome FROM paciente p WHERE p.id = s.paciente_id) AS paciente_nome
    FROM sessao s
    WHERE s.status_sessao = :status_sessao
      AND s.psicologo_id  = :me
  ";
  $params = [':status_sessao' => $status_sessao, ':me' => $id_psico];
} else {
  // --- FILTRO: TUDO ---
  $sql = "
    SELECT s.*, (SELECT p.nome FROM paciente p WHERE p.id = s.paciente_id) AS paciente_nome
    FROM sessao s
    WHERE s.psicologo_id = :me
  ";
  $params = [':me' => $id_psico];
}

// Executa consulta
$lista  = $conn->prepare($sql);
$lista->execute($params);
$numrow = $lista->rowCount();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <title>Sessões</title>
  <!-- Bootstrap 5 CSS e Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <?php include 'css/fundo-fixo.css'; ?>
  <style>
    .hidden {
      display: none;
    }

    .btn-anim {
      transition: transform .2s ease, box-shadow .2s ease;
    }

    .btn-anim:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .table-responsive {
      overflow-x: auto;
    }

    #obsModal .modal-header {
      background-color: #00c0f0;
      color: #000;
    }

    #obsModal .modal-body {
      max-height: 400px;
      overflow-y: auto;
    }

    .modal-anotacoes {
      white-space: pre-wrap;
      word-break: break-word;
    }
  </style>
</head>

<body class="fundofixo">

  <!-- MENU NAVBAR -->
  <?php include "menu_publico.php"; ?>

  <!-- TÍTULO -->
  <main class="container my-4">
    <h1 class="text-center text-white py-2" style="background-color:#DBA632; border-radius:10px;">SESSÕES</h1>
    <p class="text-center fw-bold">Esta é a página de administração das suas sessões com seus pacientes.</p>
  </main>

  <!-- FILTRO -->
  <div class="container mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <form action="" method="GET" class="d-flex align-items-center gap-2 mb-0">
        <label for="status_sessao" class="fw-bold mb-0">STATUS</label>
        <select name="status_sessao" id="status_sessao" class="form-select" style="max-width:150px;">
          <option value="" <?php if ($status_sessao === '') echo 'selected'; ?>>TUDO</option>
          <option value="AGENDADA" <?php if ($status_sessao === 'AGENDADA') echo 'selected'; ?>>AGENDADA</option>
          <option value="REALIZADA" <?php if ($status_sessao === 'REALIZADA') echo 'selected'; ?>>REALIZADA</option>
          <option value="CANCELADA" <?php if ($status_sessao === 'CANCELADA') echo 'selected'; ?>>CANCELADA</option>
        </select>
        <button type="submit" class="btn text-light btn-anim" style="background-color:#DBA632;">FILTRAR</button>
      </form>
      <div class="col-12 col-md-6 text-md-end">
        <a href="sessao_insere.php" class="btn btn-primary btn-anim">NOVA SESSÃO <i class="bi bi-plus"></i></a>
      </div>
    </div>
  </div>

  <!-- LISTA -->
  <div class="container-fluid">
    <div class="table-responsive">
      <table class="table table-striped table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th class="hidden">ID</th>
            <th>PACIENTE</th>
            <th>ANOTAÇÕES</th>
            <th>DATA/HORA</th>
            <th>CRIADO EM</th>
            <th>STATUS</th>
            <th>EDITAR</th>
            <th>AÇÃO</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($numrow > 0): while ($row = $lista->fetch(PDO::FETCH_ASSOC)): ?>
              <tr>
                <td class="hidden"><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['paciente_nome'] ?: '—'); ?></td>
                <td>
                  <?php if (!empty(trim($row['anotacoes'] ?? ''))): ?>
                    <button class="btn btn-info btn-anim"
                      data-bs-toggle="modal"
                      data-bs-target="#obsModal"
                      data-nome="<?php echo htmlspecialchars($row['paciente_nome']); ?>"
                      data-obs="<?php echo htmlspecialchars($row['anotacoes']); ?>">
                      <i class="bi bi-chat-dots"></i>
                    </button>
                  <?php else: ?> — <?php endif; ?>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['data_hora_sessao'])); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['data_criacao'])); ?></td>
                <td><?php echo $row['status_sessao']; ?></td>
                <td>
                  <a href="sessao_atualiza.php?id=<?php echo $row['id']; ?>"
                    class="btn btn-warning btn-anim">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                </td>
                <td>
                  <?php if ($row['status_sessao'] === 'AGENDADA'): ?>
                    <!-- Botão REALIZAR -->
                    <button class="realizar btn btn-primary btn-anim"
                      data-id="<?php echo $row['id']; ?>"
                      data-nome="<?php echo htmlspecialchars($row['paciente_nome']); ?>">
                      <i class="bi bi-check2-circle"></i>
                    </button>
                    <!-- Botão CANCELAR -->
                    <button class="delete btn btn-danger btn-anim"
                      data-id="<?php echo $row['id']; ?>"
                      data-nome="<?php echo htmlspecialchars($row['paciente_nome']); ?>">
                      <i class="bi bi-x-lg"></i>
                    </button>

                  <?php elseif ($row['status_sessao'] === 'CANCELADA'): ?>
                    <!-- Botão ATIVAR -->
                    <button class="activate btn btn-success btn-anim"
                      data-id="<?php echo $row['id']; ?>"
                      data-nome="<?php echo htmlspecialchars($row['paciente_nome']); ?>">
                      <i class="bi bi-check-lg"></i>
                    </button>

                  <?php else: ?>
                    <!-- REALIZADA -->
                    —
                  <?php endif; ?>
                </td>

              </tr>
            <?php endwhile;
          else: ?>
            <tr>
              <td colspan="8" class="text-center">Nenhuma sessão encontrada.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal REALIZAR -->
  <div id="realizarModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center position-relative">
          <h5 class="modal-title w-100 text-center text-primary">CONFIRMAR</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <span class="fw-bold text-primary">MARCAR COMO REALIZADA</span> esta sessão de
          <strong><span class="nome-realizar"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <a class="confirm-realizar btn btn-primary btn-anim" href="#">Confirmar</a>
          <button type="button" class="btn btn-outline-danger btn-anim" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal DESATIVAR -->
  <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center position-relative">
          <h5 class="modal-title w-100 text-center text-danger">ATENÇÃO!</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <span class="action-text fw-bold text-danger">CANCELAR</span> esta sessão de
          <strong><span class="nome"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <a class="confirm-delete btn btn-danger btn-anim" href="#">Confirmar Cancelamento</a>
          <button type="button" class="btn btn-outline-success btn-anim" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal ATIVAR -->
  <div id="activateModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center position-relative">
          <h5 class="modal-title w-100 text-center text-success">ATENÇÃO!</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <span class="action-text fw-bold text-success">ATIVAR</span> esta sessão de
          <strong><span class="nome"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <a class="confirm-activate btn btn-success btn-anim" href="#">Confirmar Ativação</a>
          <button type="button" class="btn btn-outline-secondary btn-anim" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Anotações -->
  <div class="modal fade" id="obsModal" tabindex="-1" aria-labelledby="obsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Anotações de <strong><span class="modal-paciente-nome"></span></strong></h5>
        </div>
        <div class="modal-body">
          <p class="modal-anotacoes"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-info btn-anim" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts JS -->
  <script>
    // Preenche modal de realiza
    $('.realizar').on('click', function() {
      var id = $(this).data('id');
      var nome = $(this).data('nome');
      $('.nome-realizar').text(nome);
      $('.confirm-realizar').attr('href', 'sessao_confirma.php?id=' + id);
      new bootstrap.Modal(document.getElementById('realizarModal')).show();
    });

    // Preenche modal de desativação
    $('.delete').on('click', function() {
      var id = $(this).data('id');
      var nome = $(this).data('nome');
      $('.nome').text(nome);
      $('.confirm-delete').attr('href', 'sessao_desativa.php?id=' + id);
      new bootstrap.Modal(document.getElementById('myModal')).show();
    });

    // Preenche modal de ativação
    $('.activate').on('click', function() {
      var id = $(this).data('id');
      var nome = $(this).data('nome');
      $('.nome').text(nome);
      $('.confirm-activate').attr('href', 'sessao_ativa.php?id=' + id);
      new bootstrap.Modal(document.getElementById('activateModal')).show();
    });

    // Modal de anotações
    document.getElementById('obsModal').addEventListener('show.bs.modal', function(e) {
      var btn = e.relatedTarget;
      document.querySelector('.modal-paciente-nome').textContent = btn.getAttribute('data-nome');
      document.querySelector('.modal-anotacoes').textContent = btn.getAttribute('data-obs');
    });
  </script>
</body>

</html>