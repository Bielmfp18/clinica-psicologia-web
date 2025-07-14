<!-- CADASTRAR PACIENTE -->

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
        alert('Faça login antes de cadastrar pacientes.');
        window.location.href = 'index.php';
        </script>");
    exit;
}

include 'conn/conexao.php';        // Conexão com o banco de dados
include 'funcao_historico.php';    // Inclui a função registrarHistorico

// Recupera o ID do psicólogo da sessão
$id_psicologo = (int) $_SESSION['psicologo_id'];

// Busca dados do psicólogo (se precisar exibir algo no formulário)
$sql_psicologo = $conn->prepare("SELECT * FROM psicologo WHERE id = :id");
$sql_psicologo->bindParam(':id', $id_psicologo, PDO::PARAM_INT);
$sql_psicologo->execute();
$psicologo = $sql_psicologo->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recupera e valida o ID do psicólogo enviado pelo formulário
    $psicologo_id = isset($_POST['psicologo_id'])
        ? (int) $_POST['psicologo_id']
        : 0;
    if ($psicologo_id !== $id_psicologo) {
        die("ID do psicólogo inválido.");
    }

    // Dados do paciente
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $data_nasc = $_POST['data_nasc'];
    $observacoes = $_POST['observacoes'] ?? '';
    $status = 1; // Ativo por padrão

    try {
        // Chama procedure para inserir paciente
        $sql = "CALL ps_paciente_insert(
                  :pspsicologo_id,
                  :psnome,
                  :psemail,
                  :pstelefone,
                  :psdata_nasc,
                  :psobservacoes,
                  :psativo
                )";

        // Prepara a consulta
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pspsicologo_id', $psicologo_id, PDO::PARAM_INT);
        $stmt->bindParam(':psnome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':psemail', $email, PDO::PARAM_STR);
        $stmt->bindParam(':pstelefone', $telefone, PDO::PARAM_STR);
        $stmt->bindParam(':psdata_nasc', $data_nasc, PDO::PARAM_STR);
        $stmt->bindParam(':psobservacoes', $observacoes, PDO::PARAM_STR);
        $stmt->bindParam(':psativo', $status, PDO::PARAM_INT);

        // Executa a procedure
        if ($stmt->execute()) {
            // Limpa cursor para próximas queries
            $stmt->closeCursor();

            // Registra no histórico de operações
            registrarHistorico(
                $conn,
                $psicologo_id,
                'Cadastro',                    // ação de criação
                'Paciente',                  // entidade afetada
                "Paciente cadastrado: {$nome}" // descrição detalhada
            );

            // Retorna sucesso em JSON
            echo json_encode([
                'success' => true,
                'id'      => $id,
                'message' => 'Paciente cadastrado com sucesso!'
            ]);
            exit;
        } else {
            // Erro ao executar a ativação
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao tentar cadastrar o paciente!'
            ]);
            exit;
        }
    } catch (PDOException $e) {
        // Em caso de exceção, devolve JSON de erro
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao cadastrar o paciente: ' . addslashes($e->getMessage())
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cadastrar Paciente</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body.fundofixo {
            background: url('image/MENTE_RENOVADA.png') no-repeat center center fixed;
            background-size: cover;
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
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover,
        .btn-voltar:hover {
            background-color: #b38121 !important;
            transform: scale(1.05);
        }
    </style>
</head>

<body class="fundofixo">

    <?php include 'menu_publico.php'; ?>

    <main class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-sm-10 col-md-6 col-lg-5">

            <!-- Cabeçalho com botão e título -->
            <div class="position-relative mb-4">
                <a href="paciente.php" class="btn btn-voltar position-absolute start-0 top-50 translate-middle-y">
                    <i class="bi bi-arrow-left text-white"></i>
                </a>
                <h2 class="text-white fw-bold p-2 rounded text-center" style="background-color: #DBA632;">
                    Cadastrar Paciente
                </h2>
            </div>

            <div class="card p-4">
                <form method="POST" name="form_insere_paciente" id="form_insere_paciente">
                    <!-- Campo oculto para enviar o ID do psicólogo -->
                    <input type="hidden" name="psicologo_id" value="<?php echo $id_psicologo; ?>">

                    <!-- Nome -->
                    <div class="mb-4">
                        <label for="nome" class="form-label">Nome:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="nome" id="nome" class="form-control" required maxlength="100" placeholder="Digite o nome do paciente">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="form-label">Email:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="Digite o email">
                        </div>
                    </div>

                    <!-- Telefone -->
                    <div class="mb-4">
                        <label for="telefone" class="form-label">Telefone:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                            <input type="text" name="telefone" id="telefone" class="form-control" required maxlength="14" placeholder="Digite o telefone">
                        </div>
                    </div>

                    <!-- Data de nascimento -->
                    <div class="mb-4">
                        <label for="data_nasc" class="form-label">Data de Nascimento:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-event-fill"></i></span>
                            <input type="date" name="data_nasc" id="data_nasc"

                                class="form-control" required value="2000-01-01">
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="mb-4">
                        <label for="observacoes" class="form-label">Observações:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
                            <textarea name="observacoes" id="observacoes" class="form-control" placeholder="Observações sobre o paciente"></textarea>
                        </div>
                    </div>

                    <!-- Botão -->
                    <div class="d-grid">
                        <button type="submit" class="btn text-white">
                            <i class="bi bi-person-plus-fill me-2 text-white"></i> Cadastrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Faz o textarea ajustar a altura ao conteúdo de observações do paciente.
        document.addEventListener('DOMContentLoaded', function() {
            const ta = document.getElementById('observacoes');

            function ajustaAltura() {
                ta.style.height = 'auto';
                ta.style.height = ta.scrollHeight + 'px';
            }
            ajustaAltura();
            ta.addEventListener('input', ajustaAltura);
        });
    </script>
</body>

</html>