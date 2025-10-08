<?php
// SESSAO_ATIVA - retorna JSON limpo

// Limpa buffers anteriores (evita warnings no output JSON)
while (ob_get_level()) ob_end_clean();

// Cabeçalhos JSON
header('Content-Type: application/json; charset=utf-8');

// Sessão
session_name('Mente_Renovada');
session_start();

// Verifica login
if (!isset($_SESSION['psicologo_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Faça login novamente.'
    ]);
    exit;
}

include 'conn/conexao.php';
include 'funcao_historico.php';

$psicologoId = (int) $_SESSION['psicologo_id'];

// Valida ID recebido
$id = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
} elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = (int) $_POST['id'];
}

if ($id === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID da sessão não informado ou inválido.'
    ]);
    exit;
}

try {
    // Garante que $conn é um PDO
    if (!($conn instanceof PDO)) {
        throw new Exception('Conexão PDO inválida. Verifique conn/conexao.php.');
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1️⃣ Ativa a sessão via procedure
    $stmt = $conn->prepare("CALL ps_sessao_enable(:sessao_id)");
    $stmt->bindValue(':sessao_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();

    // 2️⃣ Busca informações da sessão e paciente
    $stmtInfo = $conn->prepare("
        SELECT 
            s.paciente_id, 
            s.data_hora_sessao, 
            p.nome AS nomePaciente, 
            p.ativo AS paciente_ativo
        FROM sessao s
        JOIN paciente p ON s.paciente_id = p.id
        WHERE s.id = :id
        LIMIT 1
    ");
    $stmtInfo->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtInfo->execute();
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Sessão não encontrada.'
        ]);
        exit;
    }

    $nomePaciente   = $info['nomePaciente'] ?? "ID {$id}";
    $dataHoraSessao = $info['data_hora_sessao'] ?? null;
    $pacienteId     = isset($info['paciente_id']) ? (int)$info['paciente_id'] : null;
    $pacienteAtivo  = isset($info['paciente_ativo']) ? (int)$info['paciente_ativo'] : 1;

    // 3️⃣ Reativa paciente se estiver inativo
    $pacienteReativado = false;
    if ($pacienteId !== null && $pacienteAtivo === 0) {
        $stmtAct = $conn->prepare("CALL ps_paciente_enable(:pid)");
        $stmtAct->bindValue(':pid', $pacienteId, PDO::PARAM_INT);
        $stmtAct->execute();
        $stmtAct->closeCursor();
        $pacienteReativado = true;

        // Registra histórico de reativação do paciente
        registrarHistorico(
            $conn,
            $psicologoId,
            'Ativação',
            "Paciente ativado: {$nomePaciente}",
            'Paciente'
        );
    }

    // 4️⃣ Registra histórico da ativação da sessão
    $descSess = "Sessão de {$nomePaciente} ativada";

    registrarHistorico(
        $conn,
        $psicologoId,
        'Ativação',
        $descSess,
        'Sessão'
    );

    // 5️⃣ Retorna JSON de sucesso
    echo json_encode([
        'success' => true,
        'id' => $id,
        'message' => 'Sessão ativada com sucesso!',
        'paciente_reativado' => $pacienteReativado,
        'paciente_id' => $pacienteId
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao ativar a sessão: ' . $e->getMessage()
    ]);
    exit;
}
