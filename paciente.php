<?php
// PACIENTE


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

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
  // preparar flash de aviso
  $_SESSION['flash'] = [
    'type'    => 'warning',  // ou 'danger', como preferir
    'message' => 'Faça login antes de acessar os pacientes.'
  ];
  header('Location: index.php');
  exit;
}


// Pega o ID do psicólogo logado
$id_psico = (int) $_SESSION['psicologo_id'];

// Inicia conexão com o banco de dados (PDO) e inclui o init.php para tratamento de erros
include 'conn/conexao.php';
include 'conn/init.php';


// Captura o filtro de status ("" / "1" / "0")
$ativo = isset($_GET['ativo']) ? trim($_GET['ativo']) : '';

// Monta a consulta de acordo com o filtro e o psicólogo logado
if ($ativo === '1') {
  // --- SOMENTE OS MEUS PACIENTES ATIVOS ---
  $sql = "
    SELECT *
      FROM paciente
     WHERE ativo = 1
       AND psicologo_id = :me
  ";
  $params = [
    ':me' => $id_psico
  ];
} elseif ($ativo === '0') {
  // --- SOMENTE OS MEUS PACIENTES INATIVOS ---
  $sql = "
    SELECT *
      FROM paciente
     WHERE ativo = 0
       AND psicologo_id = :me
  ";
  $params = [
    ':me' => $id_psico
  ];
} else {
  // --- SEM FILTRO: MOSTRA TODOS OS MEUS PACIENTES (ativos e inativos) ---
  $sql = "
    SELECT *
      FROM paciente
     WHERE psicologo_id = :me
     AND ativo = '1'
  ";
  $params = [
    ':me' => $id_psico
  ];
}

// Prepara e executa a consulta
$lista   = $conn->prepare($sql);
$lista->execute($params);

// Número de linhas retornadas
$numrow = $lista->rowCount();

//Espaçamento do rapé para o conteúdo principal
$mostrarRodape = isset($_SESSION['psicologo_id']) && ($numrow  > 4);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Paciente</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Link para o ícone da aba -->
  <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">
  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Caminho para a pasta CSS -->
  <?php include 'css/fundo-fixo.css' ?>

  <!-- Estilos personalizados -->
  <style>
    body.fundofixo {
      background-color: var(--bg-light);
      color: var(--text-dark);
      padding-top: 85px;
      z-index: 1;
    }


    /* Esconde os itens de classe "hidden"*/
    .hidden {
      display: none;
    }

    /* Animação de crescimento */
    .btn-anim {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-anim:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Ajustes gerais nos badges */
    .status-col .badge {
      display: inline-block;
      font-weight: 600;
      text-transform: capitalize;
      /* “Ativo”, “Inativo” */
      padding: 0.35em 0.75em;
      font-size: 0.85rem;
      border-radius: 0.25rem;
    }

    /* Ativo (verde) */
    .status-col .badge.bg-success {
      background-color: #198754 !important;
      /* cor personalizada */
      color: #fff;
    }

    /* Inativo (vermelho) */
    .status-col .badge.bg-danger {
      background-color: #dc3545 !important;
      /* cor personalizada */
      color: #fff;
    }

    /* Estilo do modal de observações */
    .modal-body {
      max-height: 60vh;
      overflow-y: auto;
      overflow-x: hidden;
      white-space: normal;
      word-break: normal;
      /* respeita a divisão natural das palavras */
      overflow-wrap: break-word;
      /* quebra só quando não couber na linha */
      /* hyphens: none; */
      /* opcionalmente, desliga hífens automáticos */
    }

    /* Gradiente para modal de DESATIVAR (base #dc3545) */
    .modal-header-danger {
      background: linear-gradient(135deg, #e85a58 0%, #dc3545 100%);
      color: #fff !important;
    }

    /* Gradiente para modal de ATIVAR (base #198754) */
    .modal-header-success {
      background: linear-gradient(135deg, #3fa86d 0%, #198754 100%);
      color: #fff !important;
    }

    /* Gradiente para modal de OBSERVAÇÕES (base #0dcaf0) */
    .modal-header-info {
      background: linear-gradient(135deg, #5ddcf9 0%, #0dcaf0 100%);
      color: #fff !important;
    }

    /* Container da alert: usa flex para alinhar botão */
    /* Torna a .alert um flex container */
    .alert-dismissible {
      position: relative;
      padding-right: 2.5rem;
      /* espaço pro X */
    }

    /* Botão de fechar pequeno e centralizado verticalmente */
    .alert-wrapper .btn-close {
      position: absolute;
      top: 0.5rem;
      /* ajusta conforme o padding-top do .alert */
      right: 0.5rem;
      /* mesma distância da borda direita */
      background: none !important;
      border: none;
      padding: 0;
      font-size: 1rem;
      line-height: 1;
    }

    /* Remove sombra ao focar */
    .alert-dismissible .btn-close:focus {
      box-shadow: none;
    }

    /* Título/Descrição (Distância) */
    .mb-220 {
      margin-bottom: 5px !important;
    }

    /* Rodapé (Distância) */
    .footer-spacer {
      height: 90px;
      width: 100%;
      display: block;
    }
  </style>

</head>

<body class="fundofixo">

  <!-- MENU NAVBAR -->
  <?php include "menu_publico.php" ?>

  <!-- TÍTULO E DESCRIÇÃO-->
  <main class="container py-4 historico-content <?php echo $mostrarRodape ? 'mb-220' : ''; ?>">
    <h1 class="text-center text-white py-2" style="background-color:#DBA632; border-radius:10px;">PACIENTES</h1>
    <p class="text-center fw-bold">Esta é a página de administração dos seus pacientes.</p>
  </main>

  <!-- FILTRO -->
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <!-- FORMULÁRIO DE FILTRO -->
      <form action="" method="GET" class="d-flex align-items-center gap-2 mb-0">
        <label for="ativo" class="fw-bold mb-0">STATUS</label>
        <select name="ativo" id="ativo" class="form-control" style="height: 34px; max-width:130px;">
          <option value="1" <?php if ($ativo === '1') echo 'selected'; ?>>ATIVO</option>
          <option value="0" <?php if ($ativo === '0') echo 'selected'; ?>>INATIVO</option>
        </select>
        <button type="submit" class="btn text-light btn-anim" style="background-color:#DBA632;">FILTRAR</button>
      </form>

      <!-- BOTÃO ADICIONAR -->
      <a href="paciente_insere.php" class="btn btn-primary btn-anim">
        ADICIONAR <i class="bi bi-plus"></i>
      </a>
    </div>
  </div>

  <!-- LISTA DE PACIENTES -->
  <div class="container-fluid">
    <div class="table-responsive" style="border-radius:10px;">
      <table class="table table-striped table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th class="text-center">NOME</th>
            <th class="text-center">EMAIL</th>
            <th class="text-center">WHATSAPP</th>
            <th class="text-center">DATAS</th>
            <th class="text-center">STATUS</th>
            <th class="text-center">OBSERVAÇÕES</th>
            <th class="text-center">EDITAR</th>
            <th class="text-center">AÇÃO</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($numrow > 0): ?>
            <?php while ($row = $lista->fetch(PDO::FETCH_ASSOC)): ?>
              <tr data-id="<?php echo $row['id']; ?>">
                <!-- Nome -->
                <td><?php echo htmlspecialchars($row['nome'] ?? '—'); ?></td>

                <!-- Email -->
                <td><?php echo htmlspecialchars($row['email'] ?? '—'); ?></td>

                <!-- Telefone -->
                <td>
                  <?php
                  if (!empty($row['telefone'])) {
                    // Remove tudo que não for dígito
                    $digits = preg_replace('/\D+/', '', $row['telefone']);
                    // Garante o código do Brasil (55) no início
                    if (strpos($digits, '55') !== 0) {
                      $whatsapp = '55' . $digits;
                    } else {
                      $whatsapp = $digits;
                    }
                    // Gera o link para o WhatsApp Web/Mobile
                    $display = '(55) ' . htmlspecialchars($row['telefone']);
                    echo '<a href="https://wa.me/' . $whatsapp . '" '
                      . 'target="_blank" '
                      . 'class="text-decoration-none">'
                      . '<i class="bi bi-whatsapp"></i> ' . $display
                      . '</a>';
                  } else {
                    echo '—';
                  }
                  ?>
                </td>

                <!-- Datas -->
                <td>
                  Nascimento: <?php echo isset($row['data_nasc'])
                                ? date('d/m/Y', strtotime($row['data_nasc']))
                                : '—'; ?><br>
                  Cadastro: <?php echo isset($row['data_criacao'])
                              ? date('d/m/Y', strtotime($row['data_criacao']))
                              : '—'; ?>
                </td>

                <!-- Status -->
                <td class="status-col">
                  <?php if ($row['ativo'] == 1): ?>
                    <span class="badge bg-success">Ativo</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Inativo</span>
                  <?php endif; ?>
                </td>

                <!-- Observações -->
                <td>
                  <?php if (!empty(trim($row['observacoes'] ?? ''))): ?>
                    <button
                      class="btn btn-info btn-anim"
                      data-bs-toggle="modal"
                      data-bs-target="#obsModal"
                      data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                      data-obs="<?php echo htmlspecialchars($row['observacoes']); ?>">
                      <i class="bi bi-chat-dots"></i>
                    </button>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <!-- Editar -->
                <td>
                  <?php $tokenRow = encode_id_portable((int)$row['id']); ?>
                  <a href="paciente_atualiza.php?t=<?php echo urlencode($tokenRow); ?>" class="btn btn-warning btn-anim">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                </td>


                <!-- Ação -->
                <td class="action-col">
                  <?php if ($row['ativo'] == 1): ?>
                    <button class="delete btn btn-danger btn-anim"
                      data-id="<?php echo $row['id']; ?>"
                      data-nome="<?php echo htmlspecialchars($row['nome']); ?>">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  <?php else: ?>
                    <button class="activate btn btn-success btn-anim"
                      data-id="<?php echo $row['id']; ?>"
                      data-nome="<?php echo htmlspecialchars($row['nome']); ?>">
                      <i class="bi bi-check-lg"></i>
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center">Nenhum paciente encontrado.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal de Observações -->
  <div class="modal fade" id="obsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <!-- Cabeçalho em gradiente azul, título branco -->
        <div class="modal-header modal-header-info">
          <h5 class="modal-title text-white mx-auto">
            Observações de <strong><span id="obsNome"></span></strong>
          </h5>
        </div>
        <div class="modal-body bg-light text-center" id="obsTexto" style="max-height: 60vh; overflow-y: auto;"></div>
        <!-- Rodapé com botão azul de fechar -->
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-info btn-anim"
            data-bs-dismiss="modal">
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal de Desativar/Ativar (Bootstrap 5) -->
  <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Cabeçalho dinâmico, título branco -->
        <div class="modal-header justify-content-center">
          <h5 class="modal-title text-white">ATENÇÃO!</h5>
        </div>
        <!-- Corpo do modal -->
        <div class="modal-body text-center">
          Deseja mesmo <span class="action-text fw-bold"></span>
          <strong><span class="nome"></span></strong>?
        </div>
        <!-- Rodapé do modal -->
        <div class="modal-footer justify-content-center gap-2">
          <button type="button" class="btn action-confirm btn-anim"></button>
          <button type="button"
            class="btn modal-cancel btn-anim btn-outline-secondary"
            data-bs-dismiss="modal">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Preenche o modal de Observações antes de abri-lo
    $('#obsModal').on('show.bs.modal', function(event) {
      const button = $(event.relatedTarget); // quem disparou
      const nome = button.data('nome'); // pega o data-nome
      const obs = button.data('obs'); // pega o data-obs

      // ajusta título e corpo
      $('#obsNome').text(nome);
      $('#obsTexto').text(obs);

      // Gradiente no header de OBSERVAÇÕES
      $('#obsModal .modal-header')
        .removeClass('modal-header-danger modal-header-success')
        .addClass('modal-header-info');
    });

    // Função auxiliar para mudar header do myModal
    function setHeaderClass(cls) {
      const hdr = $('#myModal .modal-header');
      hdr.removeClass('modal-header-danger modal-header-success modal-header-info');
      hdr.addClass(cls);
      hdr.find('.modal-title').addClass('text-white');
    }

    // ----------------------------------------------------------------
    // 1) Quando clica em “.delete” → abrir modal para DESATIVAR
    // ----------------------------------------------------------------
    $(document).on('click', '.delete', function() {
      const nome = $(this).data('nome');
      const id = $(this).data('id');

      // Preenche o nome no corpo do modal
      $('.nome').text(nome);

      // Ajusta o texto e a cor do “action-text”
      $('.action-text')
        .text('DESATIVAR')
        .removeClass('text-success')
        .addClass('text-danger');

      // Ajusta gradiente do header para vermelho
      setHeaderClass('modal-header-danger');

      // Configura o botão action-confirm para ser o “confirm-delete”
      $('.action-confirm')
        .off('click') // limpa handlers anteriores
        .removeClass('confirm-activate btn-success')
        .addClass('confirm-delete btn-danger')
        .text('Confirmar Desativação')
        .data('id', id); // guarda o id para o AJAX

      // Botão “Cancelar” em VERDE outline para DESATIVAR
      $('.modal-cancel')
        .removeClass('btn-outline-danger')
        .addClass('btn-outline-success')
        .text('Cancelar');

      // Abre o modal
      new bootstrap.Modal($('#myModal')).show();
    });

    // ----------------------------------------------------------------
    // 2) Quando clica em “.activate” → abrir modal para ATIVAR
    // ----------------------------------------------------------------
    $(document).on('click', '.activate', function() {
      const nome = $(this).data('nome');
      const id = $(this).data('id');

      // Preenche o nome no corpo do modal
      $('.nome').text(nome);

      // Ajusta o texto e a cor do “action-text”
      $('.action-text')
        .text('ATIVAR')
        .removeClass('text-danger')
        .addClass('text-success');

      // Ajusta gradiente do header para verde
      setHeaderClass('modal-header-success');

      // Configura o botão action-confirm para ser o “confirm-activate”
      $('.action-confirm')
        .off('click')
        .removeClass('confirm-delete btn-danger')
        .addClass('confirm-activate btn-success')
        .text('Confirmar Ativação')
        .data('id', id);

      // Botão “Cancelar” em VERMELHO outline para ATIVAR
      $('.modal-cancel')
        .removeClass('btn-outline-success')
        .addClass('btn-outline-danger')
        .text('Cancelar');

      // Abre o modal
      new bootstrap.Modal($('#myModal')).show();
    });

    // ----------------------------------------------------------------
    // 3) Handler AJAX: Confirmar DESATIVAÇÃO
    // ----------------------------------------------------------------
    $(document).on('click', '.confirm-delete', function() {
      const btn = $(this);
      const id = btn.data('id');
      const url = 'paciente_desativa.php?id=' + id;
      const tr = $('tr[data-id="' + id + '"]');
      const statusTd = tr.find('.status-col'); // <— badge de status
      const actionTd = tr.find('.action-col');

      // Fecha o modal
      bootstrap.Modal.getInstance($('#myModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);

          // 1) Atualiza badge para "Inativo" instantâneo
          statusTd.html('<span class="badge bg-danger">Inativo</span>');

          // 2) Substitui o botão por “.activate” na linha
          actionTd.html(`
          <button class="activate btn btn-success btn-anim"
                  data-id="${id}"
                  data-nome="${tr.find('td').eq(0).text()}">
            <i class="bi bi-check-lg"></i>
          </button>
        `);

          mostrarAlert(res.message, 'danger');
        })
        .fail(() => alert('Erro ao comunicar com o servidor.'));
    });

    // ----------------------------------------------------------------
    // 4) Handler AJAX: Confirmar ATIVAÇÃO
    // ----------------------------------------------------------------
    $(document).on('click', '.confirm-activate', function() {
      const btn = $(this);
      const id = btn.data('id');
      const url = 'paciente_ativa.php?id=' + id;
      const tr = $('tr[data-id="' + id + '"]');
      const statusTd = tr.find('.status-col'); // <— badge de status
      const actionTd = tr.find('.action-col');

      // Fecha o modal
      bootstrap.Modal.getInstance($('#myModal')).hide();

      $.getJSON(url)
        .done(res => {
          if (!res.success) return alert(res.message);

          // 1) Atualiza badge para "Ativo" instantâneo
          statusTd.html('<span class="badge bg-success">Ativo</span>');

          // 2) Substitui pelos botão “.delete”
          actionTd.html(`
          <button class="delete btn btn-danger btn-anim"
                  data-id="${id}"
                  data-nome="${tr.find('td').eq(0).text()}">
            <i class="bi bi-x-lg"></i>
          </button>
        `);

          mostrarAlert(res.message, 'success');
        })
        .fail(() => alert('Erro ao comunicar com o servidor.'));
    });

    // ----------------------------------------------------------------
    // 5) Função utilitária para exibir alerts temporários
    // ----------------------------------------------------------------
    function mostrarAlert(texto, tipo) {
      const $a = $(`
      <div class="alert alert-${tipo} alert-dismissible
                  position-fixed top-0 start-50 translate-middle-x mt-3"
           style="z-index:1050; display:none;">
        ${texto}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `).appendTo('body');
      $a.fadeIn(300).delay(1800).fadeOut(300, () => $a.remove());
    }
  </script>

  <!-- // Conta total de pacientes do psicólogo (para exibir rodapé se houver pacientes) -->
  <?php if (isset($_SESSION['psicologo_id']) && $numrow > 4): ?>

    <!-- Spacer para garantir espaço antes do footer -->
    <div class="footer-spacer" aria-hidden="true"></div>

    <!-- Rodapé -->
    <?php include 'rodape.php'; ?>

  <?php endif; ?>
</body>

</html>