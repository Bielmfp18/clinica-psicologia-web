<?php
session_name('Mente_Renovada');
session_start();

// Seta só o flash
$_SESSION['flash'] = [
  'type'    => 'danger',
  'message' => 'Você se desconectou da conta!'
];

// Remove a autenticação, mas mantém o flash
unset($_SESSION['login_admin'], $_SESSION['psicologo_id']);

// Redireciona para index, onde o menu_publico vai ler e exibir o flash
header('Location: index.php');
exit;
?>
