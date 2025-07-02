<?php
// PACIENTE

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

  <!-- Ícone da aba
    <link rel="icon" href="image/Design sem nome.ico" type="image/ico"> -->

  <!-- Caminho para a pasta css -->
  <?php include 'css/fundo-fixo.css' ?>
</head>

<style>
  /* Esconde os itens de classe "hidden"*/
  .hidden {
    display: none;
  }
</style>

<!-- BODY -->

<body class="fundofixo">

  <!-- MENU NAVBAR -->
  <?php include "menu_publico.php" ?>

  <!-- TÍTULO E DESCRIÇÃO-->
  <main class="container my-4">
    <h1 class="text-center text-white py-2" style="background-color:#DBA632; border-radius:10px;">Pacientes</h1>
    <p class="text-center fw-bold">Esta é a página de adiministração dos seus pacientes.</p>
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
        <button type="submit" class="btn text-light" style="background-color:#DBA632;">FILTRAR</button>
      </form>

      <!-- BOTÃO ADICIONAR -->
      <a href="paciente_insere.php" class="btn btn-primary">
        ADICIONAR <i class="bi bi-plus"></i>
      </a>

    </div>
  </div>

  <!-- LISTA DE PACIENTES -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle text-center ">
      <thead>
        <tr>
          <th class="hidden">ID</th>
          <th class="hidden">PSICÓLOGO_ID</th>
          <th class="text-center">NOME</th>
          <th class="d-none d-md-table-cell">EMAIL</th> <!--Exibe apenas no PC, no celular ele não aparece-->
          <th class="text-center">TELEFONE</th> <!--Exibe apenas no PC, no celular ele não aparece-->
          <th class="d-none d-xl-table-cell">DATA NASCIMENTO</th> <!--Exibe apenas no PC, no celular ele não aparece-->
          <th class="text-center">DATA CADASTRO</th>
          <th class="text-center">OBSERVAÇÕES</th>
          <th class="text-center">STATUS</th>
        </tr>
      </thead>

      <tbody>
        <!-- EXIBE OS DADOS DO PACIENTE -->
        <?php if ($numrow > 0): ?>
          <?php while ($row = $lista->fetch(PDO::FETCH_ASSOC)) : ?>
            <tr>
              <td class="hidden"><?php echo $row['id']; ?></td>
              <td class="hidden"><?php echo $row['psicologo_id']; ?></td>
              <td class="text-center"><?php echo isset($row['nome']) ? $row['nome'] : "Sem nome"; ?></td>
              <td class="text-center"><?php echo isset($row['email']) ? $row['email'] : "Sem email"; ?></td>
              <td class="text-center"><?php echo isset($row['telefone']) ? $row['telefone'] : "Sem telefone"; ?></td>
              <td class="text-center"><?php echo isset($row['data_nascimento']) ? date('d/m/Y', strtotime($row['data_nascimento'])) : "Sem data de nascimento"; ?></td>
              <td class="text-center"><?php echo isset($row['data_cadastro']) ? date('d/m/Y', strtotime($row['data_cadastro'])) : "Sem data de cadastro"; ?></td>
              <td class="text-center"><?php echo isset($row['observacoes']) ? $row['observacoes'] : "Sem observações"; ?></td>
              <td class="text-center">
                <?php
                echo isset($row['ativo']) ? ($row['ativo'] == 1 ? "Ativo" : "Desativado") : "Sem status";
                ?>
              </td>
              <td>
                <a href="paciente_atualiza.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-xs text-white">
                  <i class="bi bi-pencil-square"></i> EDITAR
                </a>
                <button data-nome="<?php echo htmlspecialchars($row['nome']); ?>" data-id="<?php echo $row['id']; ?>" class="delete btn btn-danger btn-sm">
                  <span class="hidden-xs">DESATIVAR</span> <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="10" class="text-center">Nenhum paciente encontrado.</td>
          </tr>
        <?php endif; ?>
        <!-- FIM DOS DADOS DO PACIENTE -->
      </tbody>
    </table>
  </div>

    <!-- MODAL PARA BOOTSTRAP 5-->
    <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-danger">ATENÇÃO!</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div><!-- fecha modal-header -->
                <div class="modal-body">
                    Deseja mesmo DESATIVAR o aluno?
                    <h4><span class="nome text-danger"></span></h4>
                </div><!-- fecha modal-body -->
                <div class="modal-footer">
                    <a href="#" type="button" class="btn btn-danger delete-yes">Confirmar</a>
                    <button class="btn btn-success" data-bs-dismiss="modal">Cancelar</button>
                </div><!-- fecha modal-footer -->
            </div><!-- fecha modal-content -->
        </div><!-- Fecha modal-dialog -->
    </div><!-- Fecha Modal -->


    <!-- SCRIPT PARA O MODAL UTLIZANDO A API BOOTSTRAP 5 -->
    <script type="text/javascript">
        // Função que desativa o aluno (muda o status/ativo de "1" para "0")
        $('.delete').on('click', function() {
            var nome = $(this).data('nome'); // buscar o valor do atributo data-nome
            var id = $(this).data('id'); // buscar o valor do atributo data-id
            $('span.nome').text(nome); // insere o nome no modal
            $('a.delete-yes').attr('href', 'paciente_desativa.php?id=' + id); // define o link do botão confirmar
            var myModal = new bootstrap.Modal(document.getElementById('myModal'));
            myModal.show(); // abre o modal
        });
    </script>

    <!-- Link para o Modal JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>

</body>
</html>