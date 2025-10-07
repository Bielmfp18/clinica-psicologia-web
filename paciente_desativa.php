<?php
// PACIENTE DESATIVA - versão direta que garante desativar sessões relacionadas
header('Content-Type: application/json; charset=utf-8');

session_name('Mente_Renovada');
session_start();

if (!isset($_SESSION['psicologo_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do paciente inválido.']);
    exit;
}

$psicologoId = (int) $_SESSION['psicologo_id'];

try {
    include 'conn/conexao.php';       // espera $conn = new PDO(...)
    include 'funcao_historico.php';

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1) Chama procedure para desativar o paciente (sem transação aqui)
    $stmt = $conn->prepare("CALL ps_paciente_disable(:psid)");
    $stmt->bindValue(':psid', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();

    // 2) Pega nome do paciente
    $stmtNome = $conn->prepare("SELECT nome FROM paciente WHERE id = :id LIMIT 1");
    $stmtNome->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtNome->execute();
    $paciente = $stmtNome->fetch(PDO::FETCH_ASSOC);
    $nomePaciente = $paciente['nome'] ?? "ID {$id}";

    // 3) Pega IDs das sessões que ainda não estão CANCELADAS
    $selSess = $conn->prepare("
        SELECT id, data_hora_sessao
        FROM sessao
        WHERE paciente_id = :pid
          AND status_sessao != 'CANCELADA'
    ");
    $selSess->bindValue(':pid', $id, PDO::PARAM_INT);
    $selSess->execute();
    $sessoes = $selSess->fetchAll(PDO::FETCH_ASSOC);

    $affectedSessions = [];

    if (!empty($sessoes)) {
        // 4) Atualiza todas essas sessões para CANCELADA (uma única query)
        $upd = $conn->prepare("
            UPDATE sessao
            SET status_sessao = 'CANCELADA', data_atualizacao = NOW()
            WHERE paciente_id = :pid
              AND status_sessao != 'CANCELADA'
        ");
        $upd->bindValue(':pid', $id, PDO::PARAM_INT);
        $upd->execute();

        // 5) Registrar histórico por sessão usando os dados originais (ids antes do UPDATE)
        foreach ($sessoes as $s) {
            $sessId = (int)$s['id'];
            $dataHora = $s['data_hora_sessao'] ?? null;
            $desc = "Sessão (ID: {$sessId}) de {$nomePaciente} desativada";
            if ($dataHora) {
                $desc .= " — agendada para " . date('d/m/Y H:i', strtotime($dataHora));
            }

            // ajuste se sua função tiver assinatura diferente
            registrarHistorico($conn, $psicologoId, 'Desativação', $desc, 'Sessão');

            $affectedSessions[] = $sessId;
        }
    }

    // 6) Histórico do paciente
    registrarHistorico($conn, $psicologoId, 'Desativação', "Paciente desativado: {$nomePaciente}", 'Paciente');

    echo json_encode([
        'success' => true,
        'id' => $id,
        'message' => "Paciente <strong>{$nomePaciente}</strong> desativado com sucesso!",
        'sessions_deactivated_count' => count($affectedSessions),
        'sessions_deactivated_ids' => $affectedSessions
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao desativar o paciente: ' . $e->getMessage()
    ]);
    exit;
}
