<?php
include "conn/conexao.php"; // Inclui o arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $nome = $_POST['nome']; // Captura o nome do psicólogo
    $email = (filter_var($_POST['email'])); // Usa filter_var para validar o email
    $senha = password_hash(trim($_POST['senha']), PASSWORD_DEFAULT); // Usa trim para remover espaços em branco
    $CRP =  password_hash(trim($_POST['CRP']), PASSWORD_DEFAULT);
    $ativo = 1; // Define o status ativo do psicólogo

    try {
        // Prepara a consulta SQL para inserir os dados de cadastro do psicólogo
        $sql = "CALL ps_psicologo_insert(:psnome, :psemail, :pssenha, :psCRP, :psativo)";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Cadastro</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: 'HammersmithOne-Regular', sans-serif;
        }

        main.container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .breadcrumb {
            max-width: 800px;
            width: 100%;
            min-height: 400px;
            border-radius: 8px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body class="fundofixo">
    <main class="container">
        <div class="breadcrumb">
            <section>
                <article>
                    <div class="row">
                        <div class="thumbnail">
                            <br>
                            <!-- Card com imagem de fundo -->
                            <div class="card border-0 shadow" style="width: 800px; height: 600px; background: url('image/Renovada.png') center/cover no-repeat;">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">

                                    <div class="titulos">
                                        <!-- Formulário para cadastrar novo psicólogo -->
                                        <form action="cadastro.php" method="POST" enctype="multipart/form-data">

                                            <!-- Nome -->
                                            <label for="nome" style="color: #DBA632;">Nome:</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-solid fa-user text-dark"></i></span>
                                                <input type="text" name="nome" id="nome" class="form-control" required placeholder="Digite seu nome.">
                                            </div>

                                            <!-- Email -->
                                            <label for="email" style="color: #DBA632;">Email:</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-solid fa-envelope text-dark"></i></span>
                                                <input type="email" name="email" id="email" class="form-control" required placeholder="Digite seu email.">
                                            </div>

                                            <!-- Senha -->
                                            <label for="senha" style="color: #DBA632;">Senha:</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-solid fa-lock text-dark"></i></span>
                                                <input type="password" name="senha" id="senha" class="form-control" required placeholder="Digite sua senha.">
                                            </div>

                                            <!-- CRP -->
                                            <label for="CRP" style="color: #DBA632;">CRP:</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-solid fa-id-card text-dark"></i></span>
                                                <input type="text" name="CRP" id="CRP" class="form-control" maxlength="9" pattern="\d{2}/\d{1,6}" required placeholder="Digite seu CRP.">
                                            </div>

                                            <!-- InputMask para CRP -->
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/jquery.inputmask.min.js"></script>
                                            <script>
                                                $(document).ready(function() {
                                                    $("#CRP").inputmask("99/999999"); // Máscara CRP
                                                });
                                            </script>

                                            <!-- Botões -->
                                            <div style="margin-bottom: 50px;">
                                                <a href="index.php" style="background-color: rgb(255, 0, 0); color: white;" class="btn btn-dark">Cancelar</a>
                                                <input type="submit" value="Cadastrar" style="background-color: #DBA632; color: white;" class="btn btn-dark">
                                            </div>

                                        </form>
                                    </div>
                                </div>
                                <br>
                                <p class="text-center">
                                    <small>
                                        Já possui uma conta? <a href="login.php" style="color: #DBA632;">Faça login</a>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </main>
</body>

<!-- JS Bootstrap -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>

</html>