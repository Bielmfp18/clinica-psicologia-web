<?php 
session_name('Mente_Renovada');// Define o nome da sessão
session_start(); // Inicia a sessão

session_unset(); // Remove todas as variáveis de sessão
session_destroy(); // Destrói a sessão

//Exibe a mensagem de desconexão após o logout e redireciona a página de login.
echo "<script>
            alert('Você se desconectou da sua conta!');
            window.location.href='index.php';
      </script>";
exit();
?>