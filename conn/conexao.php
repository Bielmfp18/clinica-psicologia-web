<?php 
// Iniciando conexão 
$host = "127.0.0.1";
$database = "Psicologia";
$user = "root";
$password = "";
$charset = "UTF8";
$porta = 3306;

try {
    // Conexão com o Banco de Dados
    $conn = new PDO("mysql:host=$host;port=$porta;dbname=$database;charset=$charset", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Define o modo de erro do PDO para exceção
} catch (PDOException $e) {
    // Caso ocorra erro, define o código HTTP de erro 500 (erro interno do servidor)
    http_response_code(500);

    // Pega a mensagem de erro e envia para a página de erro personalizada
    $errorMsg = "Erro na conexão com o banco de dados: " . $e->getMessage();

    // Inclui a página estilizada de erro
    include __DIR__ . '/error.php';

    // Encerra a execução do script
    exit;
}
?>
