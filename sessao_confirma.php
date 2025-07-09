<!-- SESSÃO CONFIRMA -->

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
            alert('Faça login antes de confirmar sessões.');
            window.location.href = 'index.php';
         </script>");
}

// Conexão com o banco de dados
include 'conn/conexao.php';

// Verifica se o ID da sessão foi informado via GET
if (!isset($_GET['id'])) {
    die("ID da sessão não informado.");
}
// Pega o id e garante que seja inteiro
$id = (int) $_GET['id'];

try {
    // Chama procedure para confirmar a sessão
    $sql = "CALL ps_sessao_confirm(:psid)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':psid', $id, PDO::PARAM_INT);

    // Executa a procedure
    if ($stmt->execute()) {
        // Limpa o cursor para permitir próximas queries (se necessário)
        $stmt->closeCursor();
        echo "<script>
                alert('Sessão confirmada com sucesso!');
                window.location.href = 'sessao.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Erro ao tentar confirmar a sessão!');
                window.location.href = 'sessao.php';
              </script>";
        exit;
    }

} catch (PDOException $e) {
    echo "<script>
            alert('Erro ao confirmar a sessão: " . addslashes($e->getMessage()) . "');
            window.location.href = 'sessao.php';
          </script>";
    exit;
}
?>
