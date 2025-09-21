<?php
// conn/conexao.php
// Conexão PDO (MySQL) — define $pdo e $conn para compatibilidade

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

    // cria PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // também define $conn para compatibilidade com código que usa $conn (opcional)
    $conn = $pdo;

    // NÃO retornar nada — incluir o arquivo definirá $pdo e $conn no escopo do arquivo chamador.
} catch (PDOException $e) {
    // Log e mensagem genérica
    error_log('Conexão PDO falhou: ' . $e->getMessage());
    http_response_code(500);
    die('Erro: conexão com o banco de dados falhou. Verifique as configurações.');
}
