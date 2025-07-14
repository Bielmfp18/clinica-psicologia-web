<?php
// SESSÃO CONFIRMA

// SESSÃO CONFIRMA

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
            alert('Faça login antes de confirmar sessões.');
            window.location.href = 'index.php';
         </script>");
}

// Inclui conexão com o banco e função de histórico
include 'conn/conexao.php';
include 'funcao_historico.php';

// Recupera ID do psicólogo logado
$psicologoId = (int) $_SESSION['psicologo_id'];

// Verifica se o ID da sessão foi informado via GET
if (!isset($_GET['id'])) {
    die("ID da sessão não informado.");
}
// Garante que o ID seja inteiro
$id = (int) $_GET['id'];

// Garante que o ID seja inteiro
$id = (int) $_GET['id'];

try {
    // Chama procedure para confirmar a sessão
    $sql = "CALL ps_sessao_confirm(:psid)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);

    // Executa a procedure
    if ($stmt->execute()) {
        // Limpa o cursor para permitir próximas consultas
        $stmt->closeCursor();

        // Recupera o nome do psicólogo que está confirmando a sessão
        $stmtPsic = $conn->prepare("SELECT nome FROM psicologo WHERE id = :id");
        $stmtPsic->bindValue(':id', $psicologoId, PDO::PARAM_INT);
        $stmtPsic->execute();
        $psic = $stmtPsic->fetch(PDO::FETCH_ASSOC);
        $nomePsicologo = $psic['nome'] ?? "ID {$psicologoId}";

        // Recupera nome do paciente da sessão
        $stmtInfo = $conn->prepare(
            "SELECT p.nome AS nomePaciente
             FROM sessao s 
             JOIN paciente p ON s.paciente_id = p.id 
             WHERE s.id = :id"
        );
        $stmtInfo->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtInfo->execute();
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
        $nomePaciente = $info['nomePaciente'] ?? "ID {$id}";

        // Registra no histórico usando o nome do psicólogo
        registrarHistorico(
            $conn,
            $psicologoId,
            'Confirmação',    // ação de confirmação
            'Sessão',         // entidade afetada
            "Sessão de {$nomePaciente} confirmada" // descrição detalhada
        );

        // Retorna sucesso em JSON
        echo json_encode([
            'success' => true,
            'id'      => $id,
            'message' => 'Sessão confirmada com sucesso!'
        ]);
        exit;
    } else {
        // Erro ao executar a confirmação
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao tentar confirmar a sessão!'
        ]);
        exit;
    }
} catch (PDOException $e) {
    // Em caso de exceção, devolve JSON de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao confirmar a sessão: ' . addslashes($e->getMessage())
    ]);
    exit;
}
?>

