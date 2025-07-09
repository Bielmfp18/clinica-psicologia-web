<!-- SESSÃO INSERE -->

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
        alert('Faça login antes de cadastrar sessões.');
        window.location.href = 'index.php';
        </script>");
    exit;
}

// Inclui conexão com o banco e função de histórico
include 'conn/conexao.php';
include 'funcao_historico.php';

// Recupera ID do psicólogo da sessão
$psicologo_id = (int) $_SESSION['psicologo_id'];

// Busca lista de pacientes para o select
$sql_pacientes = $conn->prepare(
    "SELECT id, nome FROM paciente WHERE psicologo_id = :psid AND ativo = 1 ORDER BY nome"
);
$sql_pacientes->bindParam(':psid', $psicologo_id, PDO::PARAM_INT);
$sql_pacientes->execute();
$lista_pacientes = $sql_pacientes->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Vulnerabilidade removida: não é necessário validar o psicólogo pelo POST,
    // usamos diretamente o ID da sessão atual para maior segurança.

    // Recebe ID do paciente
    $paciente_id     = isset($_POST['paciente_id'])  ? (int) $_POST['paciente_id']  : 0;

    // Dados da sessão
    $data_hora         = $_POST['data_hora_sessao'];
    $data_atualizacao  = date('Y-m-d H:i:s');
    $anotacoes         = $_POST['anotacoes'] ?? '';
    $status            = 1; // Ativo por padrão

    try {
        // Chama procedure para inserir sessão
        $sql = "CALL ps_sessao_insert(
                  :pspsicologo_id,
                  :pspaciente_id,
                  :psanotacoes,
                  :psdata_hora,
                  :psdata_atualizacao,
                  :psstatus
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pspsicologo_id',    $psicologo_id,      PDO::PARAM_INT);
        $stmt->bindParam(':pspaciente_id',     $paciente_id,       PDO::PARAM_INT);
        $stmt->bindParam(':psanotacoes',       $anotacoes,         PDO::PARAM_STR);
        $stmt->bindParam(':psdata_hora',       $data_hora,         PDO::PARAM_STR);
        $stmt->bindParam(':psdata_atualizacao',$data_atualizacao,  PDO::PARAM_STR);
        $stmt->bindParam(':psstatus',          $status,            PDO::PARAM_INT);

        if ($stmt->execute()) {
            $stmt->closeCursor();

            // Captura nome do paciente para registrar no histórico
            $nomePaciente = '';
            foreach ($lista_pacientes as $p) {
                if ($p['id'] == $paciente_id) {
                    $nomePaciente = $p['nome'];
                    break;
                }
            }

            // Formata data/hora substituindo 'T' por espaço para descrição limpa
            $descricaoDataHora = str_replace('T', ' ', $data_hora);

            // Registra no histórico usando o nome do paciente e data formatada
            registrarHistorico(
                $conn,
                $psicologo_id,
                'Cadastro',
                'Sessão',
                "Sessão cadastrada para o paciente {$nomePaciente} em {$descricaoDataHora}"
            );

            echo "<script>
                    alert('Sessão cadastrada com sucesso!');
                    window.location.href = 'sessao.php';
                  </script>";
            exit;
        } else {
            echo "<script>
                    alert('Erro ao adicionar a sessão.');
                  </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
                alert('Erro ao adicionar a sessão: " . addslashes($e->getMessage()) . "');
              </script>";
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
</head>
<body>
    <?php include 'menu_publico.php'; ?>
    <main class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-sm-10 col-md-6 col-lg-5">
            <!-- Cabeçalho com botão e título -->
            <div class="position-relative mb-4">
                <a href="sessao.php" class="btn btn-voltar position-absolute start-0 top-50 translate-middle-y">
                    <i class="bi bi-arrow-left text-white"></i>
                </a>
                <h2 class="text-white fw-bold p-2 rounded text-center" style="background-color: #DBA632;">
                    Adicionar Sessão
                </h2>
            </div>
            <div class="card p-4">
                <form method="POST" id="form_insere_sessao">
                    <!-- Oculta o ID do psicólogo via sessão, sem precisar do POST -->
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
                                class="form-control" min="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                    </div>
                    <!-- Anotações -->
                    <div class="mb-4">
                        <label for="anotacoes" class="form-label">Anotações:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
                            <textarea name="anotacoes" id="anotacoes" class="form-control" placeholder="Anotações pré-sessão"></textarea>
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
</body>
</html>
