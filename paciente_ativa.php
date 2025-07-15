<?php
// PACIENTE ATIVA

// Exibe erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Força retorno JSON
header('Content-Type: application/json; charset=utf-8');

// Inicia sessão
session_name('Mente_Renovada');
session_start();

// Se não estiver logado, retorna erro 401
if (!isset($_SESSION['psicologo_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Faça login antes de ativar pacientes.'
    ]);
    exit;
}

// Valida ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID do paciente não informado ou inválido.'
    ]);
    exit;
}

$id = (int) $_GET['id'];
$psicologoId = (int) $_SESSION['psicologo_id'];

include 'conn/conexao.php';
include 'funcao_historico.php';

try {
    // Chama a procedure que ativa o paciente
    $stmt = $conn->prepare("CALL ps_paciente_enable(:psid)");
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        throw new Exception('Falha ao executar procedure.');
    }
    $stmt->closeCursor();

    // Busca nome para histórico
    $stmt2 = $conn->prepare("SELECT nome FROM paciente WHERE id = :id");
    $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt2->execute();
    $pac   = $stmt2->fetch(PDO::FETCH_ASSOC);
    $nome  = $pac['nome'] ?? "ID {$id}";

    // Registra no histórico
    registrarHistorico(
        $conn,
        $psicologoId,
        'Ativação',
        'Paciente',
        "Paciente ativado: {$nome}"
    );

    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'id'      => $id,
        'message' => "Paciente <strong>{$nome}</strong> ativado com sucesso!"
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao ativar o paciente: ' . addslashes($e->getMessage())
    ]);
    exit;
}
