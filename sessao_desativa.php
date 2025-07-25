<?php
// SESSÃO DESATIVA

// Exibe erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia sessão para validar psicólogo logado
session_name('Mente_Renovada');
session_start();

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
  // preparar flash de aviso
  $_SESSION['flash'] = [
    'type'    => 'warning',  // ou 'danger', como preferir
    'message' => 'Faça login antes de desativar sessões'
  ];
  header('Location: index.php');
  exit;
}

// Inclui conexão com o banco e função de histórico
include 'conn/conexao.php';
include 'funcao_historico.php';

// Recupera o ID do psicólogo logado
$psicologoId = (int) $_SESSION['psicologo_id'];

// Verifica se o ID da sessão foi informado via GET
if (!isset($_GET['id'])) {
    die("ID da sessão não informado.");
}
$id = (int) $_GET['id'];

try {
    // Chama procedure para desativar a sessão
    $sql = "CALL ps_sessao_disable(:psid)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $stmt->closeCursor(); // Libera para próximas consultas

        // Recupera nome do paciente e data/hora da sessão

        $stmtInfo = $conn->prepare(
            "SELECT s.data_hora_sessao AS dataHoraSessao, p.nome AS nomePaciente FROM sessao s 
             JOIN paciente p ON s.paciente_id = p.id WHERE s.id = :id"
        );
        $stmtInfo->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtInfo->execute();
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        // Define valores, caso não encontrado
        $nomePaciente   = $info['nomePaciente']   ?? "ID {$id}";
        $dataHoraSessao = $info['dataHoraSessao'] ?? date('Y-m-d H:i:s');

        // Registra no histórico com nome e data/hora
        registrarHistorico(
            $conn,
            $psicologoId,
            'Desativação', // ação de desativação
            'Sessão', // entidade afetada
            "Sessão de {$nomePaciente} desativada" // descrição
        );

       // Retorna sucesso em JSON
        echo json_encode([
            'success' => true,
            'id'      => $id,
            'message' => 'Sessão desativada com sucesso!'
        ]);
        exit;
    } else {
        // Erro ao executar a desativação
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao tentar desativar a sessão!'
        ]);
        exit;
    }
} catch (PDOException $e) {
    // Em caso de exceção, devolve JSON de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao desativar a sessão: ' . addslashes($e->getMessage())
    ]);
    exit;
}

?> 