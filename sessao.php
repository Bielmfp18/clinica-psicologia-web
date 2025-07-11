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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Fundo fixo -->
  <?php include 'css/fundo-fixo.css'; ?>

  <style>
    /* estilos gerais */
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
    <h1 class="text-center text-white py-2" style="background-color:#DBA632; border-radius:10px;">
      SESSÕES
    </h1>
    <p class="text-center fw-bold">
      Esta é a página de administração das suas sessões com seus pacientes.
    </p>
  </main>

  <!-- FILTRO -->
  <div class="container mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <form action="" method="GET" class="d-flex align-items-center gap-2 mb-0">
        <label for="status_sessao" class="fw-bold mb-0">STATUS</label>
        <select name="status_sessao" id="status_sessao" class="form-select" style="max-width:150px;">
          <option value="" <?= $status_sessao === '' ? 'selected' : '' ?>>TUDO</option>
          <option value="AGENDADA" <?= $status_sessao === 'AGENDADA' ? 'selected' : '' ?>>AGENDADA</option>
          <option value="REALIZADA" <?= $status_sessao === 'REALIZADA' ? 'selected' : '' ?>>REALIZADA</option>
          <option value="CANCELADA" <?= $status_sessao === 'CANCELADA' ? 'selected' : '' ?>>CANCELADA</option>
        </select>
        <button type="submit" class="btn text-light btn-anim" style="background-color:#DBA632;">
          FILTRAR
        </button>
      </form>
      <a href="sessao_insere.php" class="btn btn-primary btn-anim">
        NOVA SESSÃO <i class="bi bi-plus"></i>
      </a>
    </div>
  </div>

  <!-- LISTA -->
  <div class="container-fluid">
    <div class="table-responsive" style="border-radius:10px;">
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
          <?php if ($numrow > 0): ?>
            <?php while ($row = $lista->fetch(PDO::FETCH_ASSOC)): ?>
              <tr data-id="<?= $row['id'] ?>">
                <td class="hidden"><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['paciente_nome'] ?: '—') ?></td>
                <td>
                  <?php if (!empty(trim($row['anotacoes'] ?? ''))): ?>
                    <button class="btn btn-info btn-anim"
                      data-bs-toggle="modal"
                      data-bs-target="#obsModal"
                      data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>"
                      data-obs="<?= htmlspecialchars($row['anotacoes']) ?>">
                      <i class="bi bi-chat-dots"></i>
                    </button>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($row['data_hora_sessao'])) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                <td class="status-col"><?= $row['status_sessao'] ?></td>
                <td>
                  <a href="sessao_atualiza.php?id=<?= $row['id'] ?>"
                    class="btn btn-warning btn-anim">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                </td>
                <td class="action-col">
                  <?php if ($row['status_sessao'] === 'AGENDADA'): ?>
                    <button class="realizar btn btn-primary btn-anim"
                      data-id="<?= $row['id'] ?>"
                      data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>">
                      <i class="bi bi-check2-circle"></i>
                    </button>
                    <button class="delete btn btn-danger btn-anim"
                      data-id="<?= $row['id'] ?>"
                      data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  <?php elseif ($row['status_sessao'] === 'CANCELADA'): ?>
                    <button class="activate btn btn-success btn-anim"
                      data-id="<?= $row['id'] ?>"
                      data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>">
                      <i class="bi bi-check-lg"></i>
                    </button>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center">Nenhuma sessão encontrada.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modais -->

  <!-- REALIZAR -->
  <div id="realizarModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center">
          <h5 class="modal-title text-primary">CONFIRMAR</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <strong class="text-primary">MARCAR COMO REALIZADA</strong> esta sessão de
          <strong><span class="nome-realizar"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <button class="confirm-realizar btn btn-primary btn-anim" type="button" data-id="">
            Confirmar
          </button>
          <button type="button" class="btn btn-outline-danger btn-anim" data-bs-dismiss="modal">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- DESATIVAR -->
  <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center">
          <h5 class="modal-title text-danger">ATENÇÃO!</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <strong class="text-danger action-text">CANCELAR</strong> esta sessão de
          <strong><span class="nome"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <button class="confirm-delete btn btn-danger btn-anim" type="button" data-id="">
            Confirmar Cancelamento
          </button>
          <button type="button" class="btn btn-outline-success btn-anim" data-bs-dismiss="modal">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ATIVAR -->
  <div id="activateModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center">
          <h5 class="modal-title text-success">ATENÇÃO!</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <strong class="text-success action-text">ATIVAR</strong> esta sessão de
          <strong><span class="nome"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <button class="confirm-activate btn btn-success btn-anim" type="button" data-id="">
            Confirmar Ativação
          </button>
          <button type="button" class="btn btn-outline-secondary btn-anim" data-bs-dismiss="modal">
            Cancelar
          </button>
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


  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap 5 JS com Popper incluso -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- SEUS SCRIPTS PERSONALIZADOS -->
  <script>
    // Modal de anotações
    document.getElementById('obsModal').addEventListener('show.bs.modal', function(e) {
      var btn = e.relatedTarget;
      document.querySelector('.modal-paciente-nome').textContent = btn.getAttribute('data-nome');
      document.querySelector('.modal-anotacoes').textContent = btn.getAttribute('data-obs');
    });

    // 1) Abrir cada modal e guardar data-id
    $(document).on('click', '.realizar', function() {
      const id = $(this).data('id');
      const nome = $(this).data('nome');
      $('.nome-realizar').text(nome);
      $('.confirm-realizar').data('id', id);
      new bootstrap.Modal(document.getElementById('realizarModal')).show();
    });

    $(document).on('click', '.delete', function() {
      const id = $(this).data('id');
      const nome = $(this).data('nome');
      $('.nome').text(nome);
      $('.confirm-delete').data('id', id);
      new bootstrap.Modal(document.getElementById('myModal')).show();
    });

    $(document).on('click', '.activate', function() {
      const id = $(this).data('id');
      const nome = $(this).data('nome');
      $('.nome').text(nome);
      $('.confirm-activate').data('id', id);
      new bootstrap.Modal(document.getElementById('activateModal')).show();
    });

    // 2) Handlers AJAX

    // Confirmar realização
    $(document).on('click', '.confirm-realizar', function() {
      const btn = $(this);
      const id = btn.data('id');
      const url = 'sessao_confirma.php?id=' + id;
      const tr = $('tr[data-id="' + id + '"]');
      const statusTd = tr.find('.status-col');
      const actionTd = tr.find('.action-col');
      bootstrap.Modal.getInstance(document.getElementById('realizarModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);
          statusTd.text('REALIZADA');
          actionTd.html('—');

          // Mensagem que aparece no topo da tela
          const $a = $(`<div class="alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1050; display:none;">
          ${res.message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`).appendTo('body');
          $a.fadeIn(300).delay(1800).fadeOut(300, () => $a.remove());
        })
        .fail(() => alert('Erro ao comunicar com o servidor.'));
    });

    // Confirmar cancelamento
    $(document).on('click', '.confirm-delete', function() {
      const btn = $(this);
      const id = btn.data('id');
      const url = 'sessao_desativa.php?id=' + id;
      const tr = $('tr[data-id="' + id + '"]');
      const statusTd = tr.find('.status-col');
      const actionTd = tr.find('.action-col');
      bootstrap.Modal.getInstance(document.getElementById('myModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);
          statusTd.text('CANCELADA');
          actionTd.html(`
          <button class="activate btn btn-success btn-anim"
                  data-id="${id}"
                  data-nome="${tr.find('td').eq(1).text()}">
            <i class="bi bi-check-lg"></i>
          </button>
        `);

          // Mensagem que aparece no topo da tela
          const $a = $(`<div class="alert alert-danger alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1050; display:none;">
          ${res.message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`).appendTo('body');
          $a.fadeIn(300).delay(1800).fadeOut(300, () => $a.remove());
        })
        .fail(() => alert('Erro ao comunicar com o servidor.'));
    });

    // Confirmar ativação
    $(document).on('click', '.confirm-activate', function() {
      const btn = $(this);
      const id = btn.data('id');
      const url = 'sessao_ativa.php?id=' + id;
      const tr = $('tr[data-id="' + id + '"]');
      const statusTd = tr.find('.status-col');
      const actionTd = tr.find('.action-col');
      bootstrap.Modal.getInstance(document.getElementById('activateModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);
          statusTd.text('AGENDADA');
          actionTd.html(`
          <button class="realizar btn btn-primary btn-anim"
                  data-id="${id}"
                  data-nome="${tr.find('td').eq(1).text()}">
            <i class="bi bi-check2-circle"></i>
          </button>
          <button class="delete btn btn-danger btn-anim"
                  data-id="${id}"
                  data-nome="${tr.find('td').eq(1).text()}">
            <i class="bi bi-x-lg"></i>
          </button>
        `);

          // Mensagem que aparece no topo da tela
          const $a = $(`<div class="alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1050; display:none;">
          ${res.message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`).appendTo('body');
          $a.fadeIn(300).delay(1800).fadeOut(300, () => $a.remove());
        })
        .fail(() => alert('Erro ao comunicar com o servidor.'));
    });

    // 3) No load, converte linhas já realizadas
    $(function() {
      if ($('#status_sessao').val() === 'REALIZADA') {
        $('tbody tr').each(function() {
          if ($(this).find('.status-col').text().trim() === 'REALIZADA') {
            $(this).find('.action-col').html('—');
          }
        });
      }
    });
  </script>


</body>

</html>