<!-- SESSÃO ATIVA -->

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
            alert('Faça login antes de ativar sessões.');
            window.location.href = 'index.php';
         </script>");
}

// Arquivo de conexão com o banco de dados
include 'conn/conexao.php';

// Verifica se o ID da sessão foi informado via GET
if (!isset($_GET['id'])) {
    die("ID da sessão não informado.");
}

// Pega o id e garante que seja inteiro
$id = (int) $_GET['id'];

try {
    // Chama procedure para ativar a sessão
    $sql = "CALL ps_sessao_enable(:sessao_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':sessao_id', $id, PDO::PARAM_INT);

    // Executa a procedure
    if ($stmt->execute()) {
        // Limpa o cursor para permitir próximas queries
        $stmt->closeCursor();
        echo "<script>
                alert('Sessão ativada com sucesso!');
                window.location.href = 'sessao.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Erro ao tentar ativar a sessão!');
                window.location.href = 'sessao.php';
              </script>";
        exit;
    }

} catch (PDOException $e) {
    echo "<script>
            alert('Erro ao ativar a sessão: " . addslashes($e->getMessage()) . "');
            window.location.href = 'sessao.php';
          </script>";
    exit;
}
?>
