<!-- PACIENTE ATUALIZA -->

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
        alert('Faça login antes de atualizar pacientes.');
        window.location.href = 'index.php';
        </script>");
    exit;
}

// Arquivo de conexão com o banco de dados
include 'conn/conexao.php';
// Inclui a função de histórico (chama sua procedure ps_historico_insert)
include 'funcao_historico.php';

// Recupera o ID do psicólogo logado
$psicologoId = (int) $_SESSION['psicologo_id'];

// Verifica se o ID do paciente foi informado via GET
if (!isset($_GET['id'])) {
    echo "ID do paciente não informado.";
    exit;
}
// Pega o id para o funcionamento correto do SELECT na tabela paciente
$id = (int) $_GET['id'];

try {
    // Busca os dados do paciente no banco de dados
    $sql = "SELECT * FROM paciente WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$paciente) {
        echo "Paciente não encontrado.";
        exit;
    }
} catch (PDOException $e) {
    echo "Erro ao buscar o Paciente: " . $e->getMessage();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dados do paciente enviados pelo formulário
    $id           = (int) $_POST['id'];
    $nome         = $_POST['nome'];
    $email        = $_POST['email'];
    $telefone     = $_POST['telefone'];
    $data_nasc    = $_POST['data_nasc'];
    $observacoes  = $_POST['observacoes'] ?? '';

    try {
        // Chama a procedure para atualizar o paciente
        $sql = "CALL ps_paciente_update(
                    :psid,
                    :psnome,
                    :psemail,
                    :pstelefone,
                    :psdata_nasc,
                    :psobservacoes,
                    :psativo
                )";

        // Prepara a consulta
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':psid',           $id,                 PDO::PARAM_INT);
        $stmt->bindParam(':psnome',         $nome,               PDO::PARAM_STR);
        $stmt->bindParam(':psemail',        $email,              PDO::PARAM_STR);
        $stmt->bindParam(':pstelefone',     $telefone,           PDO::PARAM_STR);
        $stmt->bindParam(':psdata_nasc',    $data_nasc,          PDO::PARAM_STR);
        $stmt->bindParam(':psobservacoes',  $observacoes,        PDO::PARAM_STR);
        // mantém o mesmo status sem reativação automática
        $stmt->bindParam(':psativo',        $paciente['ativo'],  PDO::PARAM_INT);

        // Executa a atualização
        if ($stmt->execute()) {
            // Limpa cursor para próxima operação
            $stmt->closeCursor();

            // Registra no histórico de operações
            registrarHistorico(
                $conn,
                $psicologoId,
                'Atualização', // ação de atualização
                'Paciente', // entidade afetada
                "Paciente atualizado: {$nome} " // descrição detalhada
            );

            // Feedback e redirecionamento
            echo "<script>
                        alert('Paciente atualizado com sucesso!');
                        window.location.href = 'paciente.php';
                      </script>";
            exit;
        } else {
            echo "<script>alert('Erro ao atualizar o paciente.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>
                alert('Erro ao atualizar o paciente: " . addslashes($e->getMessage()) . "');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paciente - Atualiza</title>
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

        /* faz o textarea crescer conforme o texto */
        textarea#observacoes {
            resize: vertical;
            overflow: hidden;
            min-height: 100px;
            max-height: 400px;
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
                    Atualizar Paciente
                </h2>
            </div>

            <div class="card p-4">
                <form method="POST" id="form_insere_paciente">
                    <!-- Campo oculto para enviar o ID do paciente -->
                    <input type="hidden" name="id" value="<?php echo $paciente['id']; ?>">

                    <!-- Nome -->
                    <div class="mb-4">
                        <label for="nome" class="form-label">Nome:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text"
                                name="nome" id="nome"
                                class="form-control"
                                required maxlength="100"
                                placeholder="Digite o nome do paciente"
                                value="<?php echo htmlspecialchars($paciente['nome']); ?>">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="form-label">Email:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email"
                                name="email" id="email"
                                class="form-control"
                                required placeholder="Digite o email"
                                value="<?php echo htmlspecialchars($paciente['email']); ?>">
                        </div>
                    </div>

                    <!-- Telefone -->
                    <div class="mb-4">
                        <label for="telefone" class="form-label">Telefone:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                            <input type="text"
                                name="telefone" id="telefone"
                                class="form-control"
                                required maxlength="14"
                                placeholder="Digite o telefone"
                                value="<?php echo htmlspecialchars($paciente['telefone']); ?>">
                        </div>
                    </div>

                    <!-- Data de nascimento -->
                    <div class="mb-4">
                        <label for="data_nasc" class="form-label">Data de Nascimento:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-event-fill"></i></span>
                            <input type="date"
                                name="data_nasc" id="data_nasc"
                                class="form-control"
                                required
                                value="<?php echo htmlspecialchars($paciente['data_nasc']); ?>">
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="mb-4">
                        <label for="observacoes" class="form-label">Observações:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-chat-left-text-fill"></i></span>
                            <textarea name="observacoes"
                                id="observacoes"
                                class="form-control"
                                placeholder="Observações sobre o paciente"><?php echo htmlspecialchars($paciente['observacoes']); ?></textarea>
                        </div>
                    </div>

                    <!-- Botão -->
                    <div class="d-grid">
                        <button type="submit" class="btn text-white">
                            <i class="bi bi-save-fill me-2 text-white"></i> Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Faz o textarea ajustar a altura ao conteúdo dem observações do paciente.
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
