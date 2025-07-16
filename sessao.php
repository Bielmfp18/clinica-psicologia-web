<?php
// SESSÃO

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
  // preparar flash de aviso
  $_SESSION['flash'] = [
    'type'    => 'warning',  // ou 'danger', como preferir
    'message' => 'Faça login antes de acessar as sessões.'
  ];
  header('Location: index.php');
  exit;
}


// Resgata e apaga o flash, se existir
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

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
    AND s.status_sessao = 'AGENDADA'
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
  <!-- Link para o ícone da aba -->
  <link rel="shortcut icon" href="image/MTM.ico" type="image/x-icon">

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

    /* BADGES DE STATUS DE SESSÃO */
    .status-col .badge {
      display: inline-block;
      font-weight: 600;
      text-transform: capitalize;
      padding: 0.35em 0.75em;
      font-size: 0.85rem;
      border-radius: 0.25rem;
    }

    .status-col .badge.bg-warning {
      background-color: #ffc107 !important;
      color: #212529;
    }

    .status-col .badge.bg-success {
      background-color: #198754 !important;
      color: #fff;
    }

    .status-col .badge.bg-danger {
      background-color: #dc3545 !important;
      color: #fff;
    }

    /* MODAL ANOTAÇÕES */
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

    /* GRADIENTES DOS HEADERS */
    .modal-header-realizar {
      background: linear-gradient(135deg, #64b5f6 0%, #2196f3 100%);
      color: #fff !important;
    }

    .modal-header-danger {
      background: linear-gradient(135deg, #e85a58 0%, #dc3545 100%);
      color: #fff !important;
    }

    .modal-header-success {
      background: linear-gradient(135deg, #3fa86d 0%, #198754 100%);
      color: #fff !important;
    }

    /* Mensagem de aviso */
    .alert-wrapper {
      position: fixed;
      top: 1rem;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2000;
      display: inline-block;
    }

    .alert-wrapper .alert {
      position: relative;
      display: inline-block;
      padding: 0.5rem 2.5rem 0.5rem 0.75rem;
      font-size: 0.95rem;
      border-radius: 0.375rem;
    }

    .alert-wrapper .btn-close {
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
      background: none !important;
      border: none;
      padding: 0;
      font-size: 1rem;
      line-height: 1;
      opacity: .6;
    }

    @media (max-width: 576px) {

      /* Garante que, no modal-footer em celular, os botões fiquem alinhados em linha */
      .modal-footer {
        flex-wrap: nowrap !important;
        justify-content: center;
        gap: 0.5rem;
      }
    }
  </style>
</head>

<body class="fundofixo">

  <!-- Mensagem Flash -->
  <?php if (!empty($flash)): ?>
    <div class="alert-wrapper">
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  <?php endif; ?>

  <!-- Menu público -->
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
            <th>DATA/HORA</th>
            <th>CRIADO EM</th>
            <th>STATUS</th>
            <th>ANOTAÇÕES</th>
            <th>EDITAR</th>
            <th>AÇÃO</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($numrow > 0): ?>
            <?php while ($row = $lista->fetch(PDO::FETCH_ASSOC)): ?>
              <!-- ID -->
              <tr data-id="<?= $row['id'] ?>">
                <td class="hidden"><?= $row['id'] ?></td>
                <!-- NOME -->
                <td><?= htmlspecialchars($row['paciente_nome'] ?: '—') ?></td>
                <!-- DATA/HORA E CRIAÇÃO -->
                <td><?= date('d/m/Y H:i', strtotime($row['data_hora_sessao'])) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                <!-- STATUS -->
                <td class="status-col">
                  <?php if ($row['status_sessao'] === 'AGENDADA'): ?>
                    <span class="badge bg-warning">Agendada</span>
                  <?php elseif ($row['status_sessao'] === 'REALIZADA'): ?>
                    <span class="badge bg-success">Realizada</span>
                  <?php elseif ($row['status_sessao'] === 'CANCELADA'): ?>
                    <span class="badge bg-danger">Cancelada</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($row['status_sessao']) ?></span>
                  <?php endif; ?>
                </td>
                <!-- ANOTAÇÕES -->
                <td>
                  <?php if (!empty(trim($row['anotacoes'] ?? ''))): ?>
                    <button class="btn btn-info btn-anim"
                      data-bs-toggle="modal" data-bs-target="#obsModal"
                      data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>"
                      data-obs="<?= htmlspecialchars($row['anotacoes']) ?>">
                      <i class="bi bi-chat-dots"></i>
                    </button>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <!-- EDITAR -->
                <td>
                  <a href="sessao_atualiza.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-anim">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                </td>
                <!-- AÇÃO -->
                <td class="action-col">
                  <?php if ($row['status_sessao'] === 'AGENDADA'): ?>
                    <button class="realizar btn btn-primary btn-anim"
                      data-id="<?= $row['id'] ?>" data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>">
                      <i class="bi bi-check2-circle"></i>
                    </button>
                    <button class="delete btn btn-danger btn-anim"
                      data-id="<?= $row['id'] ?>" data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  <?php elseif ($row['status_sessao'] === 'CANCELADA'): ?>
                    <button class="activate btn btn-success btn-anim"
                      data-id="<?= $row['id'] ?>" data-nome="<?= htmlspecialchars($row['paciente_nome']) ?>">
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
  <div id="realizarModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header modal-header-realizar justify-content-center">
          <h5 class="modal-title">CONFIRMAR</h5>
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

  <!-- CANCELAR -->
  <div id="myModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center">
          <h5 class="modal-title">ATENÇÃO!</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <strong class="action-text">CANCELAR</strong> esta sessão de
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
  <div id="activateModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header justify-content-center">
          <h5 class="modal-title">ATENÇÃO!</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo <strong class="action-text">ATIVAR</strong> esta sessão de
          <strong><span class="nome"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <button class="confirm-activate btn btn-success btn-anim" type="button" data-id="">
            Confirmar Ativação
          </button>
          <button type="button" class="btn btn-outline-danger btn-anim" data-bs-dismiss="modal">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Anotações -->
  <div class="modal fade" id="obsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Anotações de <strong><span class="modal-paciente-nome"></span></strong></h5>
        </div>
        <div class="modal-body">
          <p class="modal-anotacoes text-center"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-info btn-anim" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS e jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Scripts personalizados -->
  <script>
    // ----------------------------------------------------------------
    // Modal de anotações
    // ----------------------------------------------------------------
    $('#obsModal').on('show.bs.modal', function(e) {
      const btn = $(e.relatedTarget);
      $('.modal-paciente-nome').text(btn.data('nome'));
      $('.modal-anotacoes').text(btn.data('obs'));
    });

    // ----------------------------------------------------------------
    // Função auxiliar para mudar header dos modais
    // ----------------------------------------------------------------
    function setHeaderClass(modalId, cls) {
      const hdr = document.querySelector(modalId + ' .modal-header');
      hdr.classList.remove('modal-header-realizar', 'modal-header-danger', 'modal-header-success');
      hdr.classList.add(cls);
      hdr.querySelector('.modal-title').classList.add('text-white');
    }

    // ----------------------------------------------------------------
    // 1) Abrir cada modal e guardar data-id
    // ----------------------------------------------------------------
    $(document).on('click', '.realizar', function() {
      const id = $(this).data('id');
      const nome = $(this).data('nome');
      $('.nome-realizar').text(nome);
      $('.confirm-realizar').data('id', id);
      setHeaderClass('#realizarModal', 'modal-header-realizar');
      new bootstrap.Modal($('#realizarModal')).show();
    });

    $(document).on('click', '.delete', function() {
      const id = $(this).data('id');
      const nome = $(this).data('nome');
      $('.nome').text(nome);
      $('.action-text').text('CANCELAR').removeClass('text-success').addClass('text-danger');
      setHeaderClass('#myModal', 'modal-header-danger');
      $('.confirm-delete').data('id', id);
      new bootstrap.Modal($('#myModal')).show();
    });

    $(document).on('click', '.activate', function() {
      const id = $(this).data('id');
      const nome = $(this).data('nome');
      $('.nome').text(nome);
      $('.action-text').text('ATIVAR').removeClass('text-danger').addClass('text-success');
      setHeaderClass('#activateModal', 'modal-header-success');
      $('.confirm-activate').data('id', id);
      new bootstrap.Modal($('#activateModal')).show();
    });

    // ----------------------------------------------------------------
    // 2) Handlers AJAX
    // ----------------------------------------------------------------

    // Confirmar realização
    $(document).on('click', '.confirm-realizar', function() {
      const btn = $(this);
      const id = btn.data('id');
      const url = 'sessao_confirma.php?id=' + id;
      const tr = $('tr[data-id="' + id + '"]');
      const statusTd = tr.find('.status-col');
      const actionTd = tr.find('.action-col');
      bootstrap.Modal.getInstance($('#realizarModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);
          statusTd.html('<span class="badge bg-success">Realizada</span>');
          actionTd.html('—');
          const $a = $(`<div class="alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1050; display:none;">${res.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`).appendTo('body');
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
      bootstrap.Modal.getInstance($('#myModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);
          statusTd.html('<span class="badge bg-danger">Cancelada</span>');
          actionTd.html(`
            <button class="activate btn btn-success btn-anim" data-id="${id}" data-nome="${tr.find('td').eq(1).text()}">
              <i class="bi bi-check-lg"></i>
            </button>
          `);
          const $a = $(`<div class="alert alert-danger alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1050; display:none;">${res.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`).appendTo('body');
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
      bootstrap.Modal.getInstance($('#activateModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);
          statusTd.html('<span class="badge bg-warning">Agendada</span>');
          actionTd.html(`
            <button class="realizar btn btn-primary btn-anim" data-id="${id}" data-nome="${tr.find('td').eq(1).text()}"><i class="bi bi-check2-circle"></i></button>
            <button class="delete btn btn-danger btn-anim" data-id="${id}" data-nome="${tr.find('td').eq(1).text()}"><i class="bi bi-x-lg"></i></button>
          `);
          const $a = $(`<div class="alert alert-success alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1050; display:none;">${res.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`).appendTo('body');
          $a.fadeIn(300).delay(1800).fadeOut(300, () => $a.remove());
        })
        .fail(() => alert('Erro ao comunicar com o servidor.'));
    });

    // ----------------------------------------------------------------
    // 3) Ajuste inicial de linhas realizadas
    // ----------------------------------------------------------------
    $(function() {
      $('tbody tr').each(function() {
        if ($(this).find('.status-col').text().trim() === 'Realizada') {
          $(this).find('.action-col').html('—');
        }
      });
    });
  </script>

  <!-- Rodapé -->
  <?php include 'rodape.php'; ?>

</body>

</html>