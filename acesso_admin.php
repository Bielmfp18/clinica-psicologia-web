<?php 

if (!isset($_SESSION['login_admin'])) {// Verifica se a sessão de login do admin não está definida
    session_start();
header('login.php'); //Redireciona para a página de login
exit(); 
}

// Inicia a sessão
if (isset($_SESSION['nome_de_sessao'])){
    $_SESSION['nome_da_sessao'] = session_name();
}

// Verifica se a sessão já está iniciada
$nome_da_sessao = session_name();
if (isset($_SESSION['login_admin']) or $_SESSION['nome_da_sessao']){
    session_destroy(); // Destrói a sessão
    header('Location: login.php'); // Redireciona para a página de login
}
?>