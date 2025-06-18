<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $nome = $_POST['nome']; // Captura o nome do psicólogo
    $email = (filter_var($_POST['email'])); // Usa filter_var para validar o email
    $senha = password_hash(trim($_POST['senha']), PASSWORD_DEFAULT); // Usa trim para remover espaços em branco
    $CRP =  password_hash(trim($_POST['CRP']), PASSWORD_DEFAULT);
    $ativo = 1; // Define o status ativo do psicólogo


    try {
        // Prepara a consulta SQL para inserir os dados de cadastro do psicólogo
        $sql = "CALL ps_psicologo_insert(:psnome, :psemail, :pssenha, psCRP, psativo)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam("psnome", $nome);
        $stmt->bindParam("psemail", $email);
        $stmt->bindParam("pssenha", $senha);
        $stmt->bindParam("psCRP", $CRP);
        $stmt->bindParam("psativo", $ativo, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $stmt->closeCursor();
            echo "<script>
                    alert('Cadastrado realizado com sucesso!');
                    window.location.href='../ClinicaPsicologia-WEB/perfil_ps.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Erro ao tentar realizar o cadastro.');
                    window.location.href='../ClinicaPsicologia-WEB/cadastro.php';
                  </script>";
        }
        //Registra o erro e o mostra ao usuário.
    } catch (PDOException $e) {
        echo "Erro ao realizar o cadastro: " . $e->getMessage();
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <title>Cadastro</title>
</head>

<body>

</body>

</html>