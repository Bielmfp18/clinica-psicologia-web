<?php
// conexão.php

// Configurações de acesso
$host     = '127.0.0.1';
$database = 'Psicologia';
$user     = 'root';
$password = '';
$port     = 3306;
$charset  = 'utf8mb4';

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $host,
        $port,
        $database,
        $charset
    );

    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $conn;
}
catch (PDOException $e) {
    // Em vez de usar $e->getMessage(), definimos UMA MENSAGEM GENÉRICA
    $GLOBALS['errorCode']       = 500;
    $GLOBALS['errorMsg']        = "Erro na conexão com o banco de dados. Por favor, tente novamente mais tarde.";
    $GLOBALS['showMenuAndBack'] = false;
    http_response_code(500);
    require __DIR__ . '/error.php';
    exit;
}
