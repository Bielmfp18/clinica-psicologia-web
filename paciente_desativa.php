<!-- PACIENTE DESATIVA -->

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
            alert('Faça login antes de desativar pacientes.');
            window.location.href = 'index.php';
         </script>");
}

// Conexão com o banco
include 'conn/conexao.php';
// Função de histórico
include 'funcao_historico.php';

// ID do psicólogo logado
$psicologoId = (int) $_SESSION['psicologo_id'];

// Verifica se o ID do paciente foi informado via GET
if (!isset($_GET['id'])) {
    die("ID do paciente não informado.");
}
$id = (int) $_GET['id'];

try {
    // Chama procedure para desativar o paciente
    $sql = "CALL ps_paciente_disable(:psid)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $stmt->closeCursor();

        // Recupera o nome do paciente antes de registrar no histórico
        $stmtNome = $conn->prepare("SELECT nome FROM paciente WHERE id = :id");
        $stmtNome->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtNome->execute();
        $paciente = $stmtNome->fetch(PDO::FETCH_ASSOC);
        $nomePaciente = $paciente['nome'] ?? "ID {$id}";

        // Registra com nome no histórico
        registrarHistorico(
            $conn,
            $psicologoId,
            'Desativação',
            'Paciente',
            "Paciente desativado: {$nomePaciente}"
        );

        echo "<script>
                alert('Paciente desativado com sucesso!');
                window.location.href = 'paciente.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Erro ao tentar desativar o paciente!');
                window.location.href = 'paciente.php';
              </script>";
        exit;
    }

} catch (PDOException $e) {
    echo "<script>
            alert('Erro ao desativar o paciente: " . addslashes($e->getMessage()) . "');
            window.location.href = 'paciente.php';
          </script>";
    exit;
}
?>
