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
    // Se não estiver logado, interrompe e redireciona
    die("<script>
            alert('Faça login antes de desativar pacientes.');
            window.location.href = 'index.php';
         </script>");
}

//Arquivo de conexão com o banco de dados
include 'conn/conexao.php';

// Verifica se o ID do paciente foi informado via GET
if (!isset($_GET['id'])) {
    die("ID do paciente não informado.");
}
// Pega o id e garante que seja inteiro
$id = (int) $_GET['id'];

try {
    // Chama procedure para desativar o paciente
    $sql = "CALL ps_paciente_disable(:psid)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);

    // Executa a procedure
    if ($stmt->execute()) {
        // Limpa o cursor para permitir próximas queries
        $stmt->closeCursor();
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
