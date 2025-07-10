<?php
// PACIENTE

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
  die("<script> 
        alert('Faça login antes de acessar os pacientes.');
        window.location.href = 'index.php';
        </script>");
}

// Pega o ID do psicólogo logado
$id_psico = (int) $_SESSION['psicologo_id'];

// Arquivo de conexão com o banco de dados
include 'conn/conexao.php';

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
  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Caminho para a pasta CSS -->
  <?php include 'css/fundo-fixo.css' ?>

  <!-- Estilos personalizados -->
  <style>
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
  </style>

</head>

<body class="fundofixo">

  <!-- MENU NAVBAR -->
  <?php include "menu_publico.php" ?>

  <!-- TÍTULO E DESCRIÇÃO-->
  <main class="container my-4">
    <h1 class="text-center text-white py-2" style="background-color:#DBA632; border-radius:10px;">PACIENTES</h1>
    <p class="text-center fw-bold">Esta é a página de administração dos seus pacientes.</p>
  </main>

  <!-- FILTRO -->
  <div class="container mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <!-- FORMULÁRIO DE FILTRO -->
      <form action="" method="GET" class="d-flex align-items-center gap-2 mb-0">
        <label for="ativo" class="fw-bold mb-0">STATUS</label>
        <select name="ativo" id="ativo" class="form-control" style="height: 34px; max-width:130px;">
          <option value="" <?php if ($ativo === '') echo 'selected'; ?>>TUDO</option>
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
            <th class="hidden">ID</th>
            <th class="text-center">NOME</th>
            <th class="text-center">EMAIL</th>
            <th class="text-center">TELEFONE</th>
            <th class="text-center">DATAS</th>
            <th class="text-center">OBSERVAÇÕES</th>
            <th class="text-center">EDITAR</th>
            <th class="text-center">AÇÃO</th>
          </tr>
        </thead>
        <tbody>
          <!-- EXIBE OS DADOS DO PACIENTE -->
          <?php if ($numrow > 0): ?>
            <?php while ($row = $lista->fetch(PDO::FETCH_ASSOC)) : ?>
              <tr>
                <td class="hidden"><?php echo $row['id']; ?></td>

                <!-- Nome -->
                <td class="text-center"><?php echo $row['nome'] ?? "Sem nome"; ?></td>

                <!-- Email -->
                <td class="text-center"><?php echo $row['email'] ?? "Sem email"; ?></td>

                <!-- Telefone -->
                <td class="text-center">(55) <?php echo $row['telefone'] ?? "Sem telefone"; ?></td>

                <!-- Datas (Nascimento / Cadastro) -->
                <td class="text-center">
                  <?php
                  $data_nasc = isset($row['data_nasc'])
                    ? date('d/m/Y', strtotime($row['data_nasc']))
                    : "—";
                  $data_cad = isset($row['data_criacao'])
                    ? date('d/m/Y', strtotime($row['data_criacao']))
                    : "—";
                  echo "Nascimento: {$data_nasc}<br>Cadastro: {$data_cad}";
                  ?>
                </td>

                <!-- Observações em modal -->
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

                <!-- Botão EDITAR -->
                <td class="btn-block-vertical">
                  <a href="paciente_atualiza.php?id=<?php echo $row['id']; ?>"
                    class="btn btn-warning btn-anim">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                </td>

                <!-- Botão DESATIVAR / ATIVAR -->
                <td class="btn-block-vertical">
                  <?php if ($row['ativo'] == 1): ?>
                    <!-- Paciente ATIVO → mostra o botão “Desativar” -->
                    <button
                      data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                      data-id="<?php echo $row['id']; ?>"
                      class="delete btn btn-danger btn-anim">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  <?php else: ?>
                    <!-- Paciente INATIVO → mostra o botão “Ativar” -->
                    <button
                      data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                      data-id="<?php echo $row['id']; ?>"
                      class="activate btn btn-success btn-anim">
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
          <!-- FIM DOS DADOS DO PACIENTE -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal de Observações -->
  <div class="modal fade" id="obsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <!-- Cabeçalho em fundo azul, texto escuro -->
        <div class="modal-header bg-info text-dark">
          <h5 class="modal-title">Observações de <strong><span id="obsNome"></span></strong></h5>
        </div>
        <!-- Corpo em fundo claro -->
        <div class="modal-body bg-light" id="obsTexto"></div>
        <!-- Rodapé com botão azul de fechar -->
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-info btn-anim" data-bs-dismiss="modal">
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
        <div class="modal-header justify-content-center position-relative">
          <h5 class="modal-title w-100 text-center text-danger">ATENÇÃO!</h5>
        </div>
        <div class="modal-body text-center">
          Deseja mesmo
          <span class="action-text fw-bold"></span>
          <strong><span class="nome"></span></strong>?
        </div>
        <div class="modal-footer justify-content-center gap-2">
          <a href="#" class="btn delete-yes btn-anim"></a>
          <button type="button" class="btn modal-cancel btn-anim" data-bs-dismiss="modal">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Preenche modal de observações
    var obsModal = document.getElementById('obsModal');
    obsModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      document.getElementById('obsNome').textContent = button.getAttribute('data-nome');
      document.getElementById('obsTexto').textContent = button.getAttribute('data-obs');
    });

    // Modal de desativar
    $('.delete').on('click', function() {
      var nome = $(this).data('nome');
      var id = $(this).data('id');

      // Preenche o span com o nome do paciente
      $('.nome').text(nome);

      // Insere a palavra "DESATIVAR" na cor vermelha
      $('.action-text')
        .text('DESATIVAR')
        .removeClass('text-success')
        .addClass('text-danger')
        .addClass('fw-bold');

      // Insere o link para desativar o paciente
      $('a.delete-yes')
        .removeClass('btn-success')
        .addClass('btn-danger')
        .text('Confirmar Desativação')
        .attr('href', 'paciente_desativa.php?id=' + id);

      // Botão cancelar verde
      $('button.modal-cancel')
        .removeClass('btn-outline-danger')
        .addClass('btn-outline-success')
        .text('Cancelar');

      new bootstrap.Modal(document.getElementById('myModal')).show();
    });

    // Modal de ativar 
    $('.activate').on('click', function() {
      var nome = $(this).data('nome');
      var id = $(this).data('id');

      // Preenche o span com o nome do paciente
      $('.nome').text(nome);

      // Insere a palavra "ATIVAR" na cor verde
      $('.action-text')
        .text('ATIVAR')
        .removeClass('text-danger')
        .addClass('text-success')
        .addClass('fw-bold');

      // Insere o link para ativar o paciente
      $('a.delete-yes')
        .removeClass('btn-danger')
        .addClass('btn-success')
        .text('Confirmar Ativação')
        .attr('href', 'paciente_ativa.php?id=' + id);

      // Botão cancelar vermelho
      $('button.modal-cancel')
        .removeClass('btn-outline-success')
        .addClass('btn-outline-danger')
        .text('Cancelar');

      new bootstrap.Modal(document.getElementById('myModal')).show();
    });
  </script>

</body>

</html>