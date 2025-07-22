<?php 
//Iniciando conexão 
$host = "127.0.0.1";
$database = "Psicologia";
$user = "root";
$password = "";
$charset = "UTF8";
$porta = 3306;

try{
    //Conexão com o Banco de Dados
    $conn = new PDO("mysql:host=$host;port=$porta;dbname=$database;charset=$charset", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//Define o modo de erro do PDO para exceção
    //Definindo o modo de erro do PDO para exceção
}catch(PDOException $e){
    //Caso ocorra erro, exibe mensagem de erro
    echo "Erro na conexão: " . $e->getMessage(); //Pega a mensagem de erro e exibe na tela.
    exit;

}
?>