<?php
include 'conn/conexao.php';

$sql = "SELECT * FROM paciente WHERE ativo = 1";

// Prepara e executa a consulta
$lista = $conn->prepare($sql);
$lista->execute();

// Número de linhas retornadas
$numrow = $lista->rowCount();

// Retorna apenas a primeira linha (associativa)
$row = $lista->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paciente</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <!-- Ícone da aba
    <link rel="icon" href="image/Design sem nome.ico" type="image/ico"> -->

    <!-- Caminho para a pasta css -->
     <?php include 'css/fundo-fixo.css'?>
</head>

<body class="fundofixo">
  <?php include "menu_publico.php" ?>
  <main class="container">
    <h1>Bem-vindo à Mente Renovada</h1>
    <p>Esta é a página inicial dos seus pacientes.</p>
  </main>
</body>

</html>