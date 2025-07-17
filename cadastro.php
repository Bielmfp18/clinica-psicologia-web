<!-- CADASTRO -->
<?php
// Define uma sessão e a inicia
session_name('Mente_Renovada');
session_start();

include "conn/conexao.php"; // Inclui o arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $nome  = trim($_POST['nome']);   // Captura o nome do psicólogo
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Usa filter_var para validar o email
    $senha = password_hash(trim($_POST['senha']), PASSWORD_DEFAULT); // Usa trim para remover espaços em branco
    $CRP   = password_hash(trim($_POST['CRP']), PASSWORD_DEFAULT);  // Armazena CRP criptografado
    $ativo = 1; // Define o status ativo do psicólogo

    try {
        // Prepara a consulta SQL para inserir os dados de cadastro do psicólogo
        $sql  = "CALL ps_psicologo_insert(:psnome, :psemail, :pssenha, :psCRP, :psativo)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam("psnome",  $nome);
        $stmt->bindParam("psemail", $email);
        $stmt->bindParam("pssenha", $senha);
        $stmt->bindParam("psCRP",   $CRP);
        $stmt->bindParam("psativo", $ativo, PDO::PARAM_INT);

        // Se existir a consulta ao Banco de Dados execute a condição
        if ($stmt->execute()) {
            $stmt->closeCursor();
            // Flash de sucesso para ser exibida na página
            $_SESSION['flash'] = [
              'type'    => 'success',
              'message' => 'Cadastro realizado com sucesso!'
            ];
            // Redireciona para index passando ?login=1 para abrir o modal de login
            header('Location: index.php?login=1');
            exit;
        } else {
            // Flash de erro para ser exibida na página
            $_SESSION['flash'] = [
              'type'    => 'danger',
              'message' => 'Erro ao realizar o cadastro. Verifique os dados e tente novamente.'
            ];
            // Redireciona para index passando ?Cadastro=1 para abrir o modal de cadastro
            header('Location: index.php?Cadastro=1');
            exit;
        }
    } catch (PDOException $e) { 
        // Em caso de exceção, também usamos flash de erro
        $_SESSION['flash'] = [
          'type'    => 'danger',
          'message' => 'Erro no servidor: ' . $e->getMessage()
        ];
        header('Location: index.php?Cadastro=1');
        exit;
    }
    exit;
}
?>


