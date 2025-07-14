<!-- PACIENTE DESATIVA -->
 
<?php

// força saída JSON
header('Content-Type: application/json; charset=utf-8');

// Exibe erros para depuração (remova em produção!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// sessão expirada → JSON de erro
if (!isset($_SESSION['psicologo_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Faça login novamente.'
    ]);
    exit;
}

// Conexão com o banco
include 'conn/conexao.php';
// Função de histórico
include 'funcao_historico.php';

// ID do psicólogo logado
$psicologoId = (int) $_SESSION['psicologo_id'];

// Valida ID recebido
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do paciente inválido.'
    ]);
    exit;
}

try {
    // Chama procedure para desativar o paciente
    $stmt = $conn->prepare("CALL ps_paciente_disable(:psid)");
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);
    $ok = $stmt->execute();
    $stmt->closeCursor();

    if (!$ok) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao tentar desativar o paciente!'
        ]);
        exit;
    }

    // Recupera o nome do paciente antes de registrar no histórico
    $stmtNome = $conn->prepare("SELECT nome FROM paciente WHERE id = :id");
    $stmtNome->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtNome->execute();
    $paciente = $stmtNome->fetch(PDO::FETCH_ASSOC);
    $nomePaciente = $paciente['nome'] ?? "ID {$id}";

    // Registra no histórico
    registrarHistorico(
        $conn,
        $psicologoId,
        'Desativação',
        'Paciente',
        "Paciente desativado: {$nomePaciente}"
    );

    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'id'      => $id,
        'message' => 'Paciente desativado com sucesso!'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao desativar o paciente: ' . addslashes($e->getMessage())
    ]);
    exit;
}
