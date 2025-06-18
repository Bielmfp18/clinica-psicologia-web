<?php
// Inicia a sessão e inclui o arquivo de conexão com o banco de dados
include "conn/conexao.php";
///Para o login receber o cookie de acesso e não ficar revisitando a página de login.
session_name('Mente_Renovada');
session_start();

// Verifica se o formulário foi enviado

if (isset($_POST['email']) || isset($_POST['senha']) || isset($_POST['CRP'])) {

    // Variáveis que recebem o valor do formulário
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); // Usa filter_var para validar o email
    $senha = (trim($_POST['senha']));
    $CRP = (trim($_POST['CRP']));

    // Prepara a consulta SQL para verificar o usuário de entrada do psicólogo
    $sql = "SELECT * FROM psicologo WHERE email = :email LIMIT 1"; // O LIMIT 1 garante que apenas um registro seja retornado
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("email", $email);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado && password_verify($senha, $resultado['senha']) && password_verify($CRP, $resultado['CRP'])) {
        // Login autorizado
        $_SESSION['login_admin'] = $email;
        $_SESSION['nome_de_sessao'] = session_name();

        echo "<script>
            alert('Seja bem-vindo $email!');
            window.location.href = '../ClinicaPsicologia-WEB/index.php';
        </script>";
    } else {
        // Dados inválidos
        echo "<script>
            alert('Email, senha ou CRP inválidos. Por favor, tente novamente');
            window.location.href = '../ClinicaPsicologia-WEB/login.php';
        </script>";
    }

    $conn = null; // Fecha a conexão com o banco
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Login</title>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            font-family: 'HammersmithOne-Regular', sans-serif;
        }

        /* Deixa o container ocupar toda a altura e centralize */
        main.container {
            min-height: 100vh;
            /* Ocupa 100% da altura da tela */
            display: flex;
            /* Flexbox para centralizar */
            align-items: center;
            /* Centraliza verticalmente */
            justify-content: center;
            /* Centraliza horizontalmente */
            padding: 20px;
            /* Espaçamento interno */
        }

        /* Ajuste da “breadcrumb” para não forçar scrollbar horizontal */
        .breadcrumb {
            max-width: 800px;
            /* Largura máxima de 800px */
            width: 100%;
            /* Se adapta à tela menor */
            min-height: 400px;
            /* Altura mínima */
            /* background: url('/image/MENTE_RENOVADA-LOGO.png') center/cover no-repeat;
            Imagem de fundo */
            border-radius: 8px;
            /* Exemplo de arredondamento */
            margin: 0 auto;
            /* Centralizar caso queira */
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
                            <p role="alert">
                                <i class=""></i>
                            </p>
                            <br>
                            <!-- Imagem de fundo do card -->
                            <div class="card border-0 shadow" style="width: 800px; height: 600px; background: url('image/Renovada.png') center/cover no-repeat;">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <!-- Campo Email de ADM -->
                                    <div class="titulos">
                                        <form action="login.php" name="email" id="email" method="POST" enctype="multipart/form-data">
                                            <label for="email" style="color: #DBA632; margin-top:10px;">Email:</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">
                                                    <span class="fa-solid fa-user text-dark" aria-hidden="true"></span>
                                                </span>
                                                <input type="text" name="email" id="email" class="form-control" autofocus required autocomplete="off" placeholder="Digite seu email.">
                                            </div>
                                            <!-- Campo Senha -->
                                            <label for="senha" style="color: #DBA632;">Senha:</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">
                                                    <i class="fa-solid fa-lock text-dark" aria-hidden="true"></i>
                                                </span>
                                                <input type="password" name="senha" id="senha" class="form-control" required autocomplete="off" placeholder="Digite sua senha.">
                                            </div>
                                            <!-- Campo CRP -->
                                            <label for="senha" style="color: #DBA632;">CRP:</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">
                                                    <i class="fa-solid fa-lock text-dark" aria-hidden="true"></i>
                                                </span>
                                                <input type="password" name="CRP" id="CRP" class="form-control" required autocomplete="off" placeholder="Digite seu CRP.">
                                            </div>
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> <!--Link JavaScript para o funcionamento do inputmask no CRP -->
                                            <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/jquery.inputmask.min.js"></script> <!--Link JavaScript para o funcionamento do inputmask no CRP -->

                                            <!-- InputMask para o CRP -->
                                            <script>
                                                $(document).ready(function() {
                                                    $("#CRP").inputmask("99/999999"); // Define o formato do CRP como "XX/XXXXXX"
                                                });
                                            </script>

                                            <!-- Botões (Cancelar e Entrar) -->
                                            <div style="margin-bottom: 50px;">
                                                <a href="index.php" style="background-color:rgb(255, 0, 0); color: white;" class="btn btn-dark">Cancelar</a>
                                                <input type="submit" value="Entrar" style="background-color: #DBA632; color: white;" class="btn btn-dark">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <br>
                                <p class="text-center">
                                    <small>
                                        Não possui uma conta? <a href="cadastro.php" style="color: #DBA632;">Cadastre-se</a>
                                    </small>
                                </p>
                            </div>
                            </div>
                        </div>
                    </article>
                </section>
            </main>
        </body>
<!-- Link arquivos Bootstrap js -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>

</html>