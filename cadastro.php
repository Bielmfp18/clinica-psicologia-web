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
    <!-- Link arquivos Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
   <!-- Link arquivo JS personalizado -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <title>Cadastro</title>
</head>

<br><br>

<body>
    <main class="container">
        <div class="row">
            <div class="col-12 col-sm-6 offset-sm-3 col-md-4 offset-md-4">
                <h2 class="text-info mb-4">
                    <a href="index.php" style="text-decoration: none;">
                        <button class="btn btn-info" type="button">
                            <!-- Atualizado para bootstrap bi bi- -->
                            <span class="bi bi-chevron-left" aria-hidden="true"></span>
                        </button>
                    <!-- Título da página -->
                    </a>
                    Cadastrar Usuários
                </h2>
                <div class="card">
                    <div class="card-body boder-0 shadow">
                        <form action="cadastro.php" method="POST" name="form_insere_alunos" id="form_insere_alunos">
                            <!-- Campo Nível ID -->
                            <label for="nivel_id">Nível do Usuário</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                            
                                    <span class="" aria-hidden="true"></span>
                                </span>
                               
                                <select name="nivel_id" id="nivel_id" class="form-control" required>
                                    <option value="1">Administrador</option>
                                    <option value="2">Gerente</option>
                                    <option value="3">Atendente</option>
                                    <option value="4">Personal Trainer</option>
                                    <option value="5">Web</option>
                                    <option value="6">Aluno</option>
                                </select>
                            </div>
                            <br>
                            <!-- Fim do campo Nível ID -->

                            <!-- Campo Nome do Aluno  -->
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome do Usuário:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <!-- Atualizado para bootstrap bi bi- -->
                                        <span class="bi bi-person text-info" aria-hidden="true"></span>
                                    </span>
                                    <input type="text" name="nome" id="nome" autofocus maxlength="100" placeholder="Digite o nome do aluno." class="form-control" required autocomplete="off">
                                </div>
                            </div>
                            <!-- Fim do campo Nome do Aluno  -->

                            <!-- Campo Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <!-- Atualizado para bootstrap bi bi- -->
                                        <span class="bi bi-envelope text-info" aria-hidden="true"></span>
                                    </span>
                                    <input type="email" name="email" id="email" placeholder="Digite o email." class="form-control" required autocomplete="on">
                                </div>
                            </div>
                            <!-- Fim do campo Email --> 

                            <!-- Campo Telefone -->
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone:</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <!-- Atualizado para bootstrap bi bi- -->
                                        <span class="bi bi-telephone text-info" aria-hidden="true"></span>
                                    </span>
                                    <input type="text" name="telefone" id="telefone" maxlength="14" placeholder="Digite o Telefone" class="form-control" required autocomplete="off">
                                </div>
                            </div>
                            <!-- Fim do campo de Telefone -->

                            <!-- Campo Senha -->
                            <label for="senha" style="color: #001b61;">Senha:</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <!-- Atualizado para bootstrap bi bi- -->
                                    <span class="bi bi-lock text-info" aria-hidden="true"></span>
                                </span>
                                <input type="password" name="senha" id="senha" class="form-control" required autocomplete="off" placeholder="Digite a senha.">
                            </div>

                            <!-- Campo Ativo/Status preenchido com valor "1" -->
                            <input type="hidden" name="ativo" id="ativo" value="1">
                            
                            <!-- Botão Cadastrar -->
                            <div class="mb-3">
                                <input type="submit" value="Cadastrar" name="enviar" id="enviar" class="btn btn-info text-light w-100">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
