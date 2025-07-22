<?php 
//Iniciando conexão 
$host = "sql103.infinityfree.com";
$database = "if0_39533260_mente_renovada";
$user = "if0_39533260";
$password = "K5FskwHTIK9a";
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