<?php
// PACIENTE ATIVA

// Exibe erros para depuração.
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
        throw new Exception('Falha ao executar procedure de ativação do paciente.');
    }
    $stmt->closeCursor();

    // Busca nome para histórico
    $stmt2 = $conn->prepare("SELECT nome FROM paciente WHERE id = :id LIMIT 1");
    $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt2->execute();
    $pac   = $stmt2->fetch(PDO::FETCH_ASSOC);
    $nome  = $pac['nome'] ?? "ID {$id}";

    // Registra no histórico (paciente)
    // Assumindo assinatura: registrarHistorico($conn, $psicologoId, $acao, $descricao, $tipo)
    registrarHistorico(
        $conn,
        $psicologoId,
        'Ativação',
        "Paciente ativado: {$nome}",
        'Paciente'
    );

    // === nova parte: reativar a ÚLTIMA sessão relacionada, se existir ===
    $stmtSess = $conn->prepare("
        SELECT id, data_hora_sessao, status_sessao
        FROM sessao
        WHERE paciente_id = :pid
        ORDER BY data_criacao DESC
        LIMIT 1
    ");
    $stmtSess->bindValue(':pid', $id, PDO::PARAM_INT);
    $stmtSess->execute();
    $ultima = $stmtSess->fetch(PDO::FETCH_ASSOC);

    $sessionReactivated = false;
    $reactivatedSessionId = null;

    if ($ultima) {
        $sessId = (int)$ultima['id'];
        $sessStatus = $ultima['status_sessao'] ?? null;

        // só atualiza se não estiver já AGENDADA
        if ($sessStatus !== 'AGENDADA') {
            $upd = $conn->prepare("
                UPDATE sessao
                SET status_sessao = 'AGENDADA', data_atualizacao = NOW()
                WHERE id = :sid
            ");
            $upd->bindValue(':sid', $sessId, PDO::PARAM_INT);
            $upd->execute();

            // registra histórico da sessão reativada
            $dataHora = $ultima['data_hora_sessao'] ?? null;
            $descSess = "Sessão reativada para o paciente {$nome}";

            // registrar histórico da sessão
            registrarHistorico(
                $conn,
                $psicologoId,
                'Ativação',
                $descSess,
                'Sessão'
            );

            $sessionReactivated = true;
            $reactivatedSessionId = $sessId;
        }
    }

    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'id'      => $id,
        'message' => "Paciente <strong>{$nome}</strong> ativado com sucesso!",
        'session_reactivated' => $sessionReactivated,
        'reactivated_session_id' => $reactivatedSessionId
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
