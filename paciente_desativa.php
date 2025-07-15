<?php
// PACIENTE DESATIVA

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
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Faça login novamente.'
    ]);
    exit;
}

// Valida ID recebido
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID do paciente inválido.'
    ]);
    exit;
}

$psicologoId = (int) $_SESSION['psicologo_id'];

include 'conn/conexao.php';
include 'funcao_historico.php';

try {
    // Chama procedure para desativar o paciente
    $stmt = $conn->prepare("CALL ps_paciente_disable(:psid)");
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new Exception('Falha ao executar procedure.');
    }
    $stmt->closeCursor();

    // Recupera o nome do paciente antes de registrar no histórico
    $stmtNome = $conn->prepare("SELECT nome FROM paciente WHERE id = :id");
    $stmtNome->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtNome->execute();
    $paciente     = $stmtNome->fetch(PDO::FETCH_ASSOC);
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
        'message' => "Paciente <strong>{$nomePaciente}</strong> desativado com sucesso!"
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao desativar o paciente: ' . addslashes($e->getMessage())
    ]);
    exit;
}
