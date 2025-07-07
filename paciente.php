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
    exit;
}
//Arquivo de conexão com o banco de dados
include 'conn/conexao.php';

// Verifica se o usuário está ativo no banco de dados para filtro.
$ativo = isset($_GET['ativo']) ? trim($_GET['ativo']) : '';

// Faz a consulta SQL para selecionar todos os pacientes ativos
// Corrigido para funcionar corretamente no filtro
if ($ativo === '0' || $ativo === '1') {
  $sql = "SELECT * FROM paciente WHERE ativo = :ativo";
  $params = [':ativo' => $ativo];
} else {
  $sql = "SELECT * FROM paciente";
  $params = [];
}

// Prepara e executa a consulta
$lista = $conn->prepare($sql);
$lista->execute($params);

// Número de linhas retornadas
$numrow = $lista->rowCount();

// Retorna apenas a primeira linha (associativa)
// Essa linha pode ser removida se não for usada fora do loop
//$row = $lista->fetch(PDO::FETCH_ASSOC);
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
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body class="fundofixo">

  <!-- MENU NAVBAR -->
  <?php include "menu_publico.php" ?>

  <!-- TÍTULO E DESCRIÇÃO-->
  <main class="container my-4">
    <h1 class="text-center text-white py-2" style="background-color:#DBA632; border-radius:10px;">Pacientes</h1>
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
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle text-center">
      <thead>
        <tr>
          <th class="hidden">ID</th>
          <th class="text-center">NOME</th>
              <th class="text-center">EMAIL</th>
          <th class="text-center">TELEFONE</th>
          <th class="text-center">DATAS</th>
          <th class="text-center">OBSERVAÇÕES</th>
          <th class="text-center">EDITAR</th>
          <th class="text-center">DESATIVAR</th>
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
              <td class="text-center"><?php echo $row['telefone'] ?? "Sem telefone"; ?></td>

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
                  <i class="bi bi-pencil-square"></i> EDITAR
                </a>
              </td>

              <!-- Botão DESATIVAR -->
              <td class="btn-block-vertical">
                <button data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                        data-id="<?php echo $row['id']; ?>"
                        class="delete btn btn-danger btn-anim">
                  <i class="bi bi-x-lg"></i> DESATIVAR
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center">Nenhum paciente encontrado.</td>
          </tr>
        <?php endif; ?>
        <!-- FIM DOS DADOS DO PACIENTE -->
      </tbody>
    </table>
  </div>

<!-- Modal de Observações -->
<div class="modal fade" id="obsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Cabeçalho em fundo amarelo, texto escuro -->
      <div class="modal-header bg-info text-dark">
        <h5 class="modal-title">Observações de <span id="obsNome"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <!-- Corpo em fundo claro -->
      <div class="modal-body bg-light" id="obsTexto"></div>
      <!-- Rodapé com botão vermelho de fechar -->
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-info btn-anim" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>


  <!-- Modal de Desativar (Bootstrap 5) -->
  <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger">ATENÇÃO!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Deseja mesmo DESATIVAR <strong><span class="nome"></span></strong>?
        </div>
        <div class="modal-footer">
          <a href="#" class="btn btn-danger delete-yes btn-anim">Confirmar</a>
          <button type="button" class="btn btn-outline-success btn-anim" data-bs-dismiss="modal">Cancelar</button>
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
      var id   = $(this).data('id');
      $('span.nome').text(nome);
      $('a.delete-yes').attr('href', 'paciente_desativa.php?id=' + id);
      var myModal = new bootstrap.Modal(document.getElementById('myModal'));
      myModal.show();
    });
  </script>

</body>
</html>
