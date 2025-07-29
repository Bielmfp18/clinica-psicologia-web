<?php
// db_connect.php
// Inicia a conexão com o Banco de Dados para o sistema Mente Renovada

// Configurações de acesso
$host     = '127.0.0.1';       // Endereço do servidor MySQL (loopback completo)
$database = 'Psicologia';     // Nome do banco de dados
$user     = 'root';           // Usuário do MySQL
$password = '';               // Senha do MySQL
$port     = 3306;             // Porta padrão do MySQL
$charset  = 'utf8mb4';        // Charset recomendado para suporte total a Unicode

try {
    // Monta a DSN de forma legível
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $host,
        $port,
        $database,
        $charset
    );

    // Cria instância PDO e força lançamento de exceções em erros
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Se tudo deu certo, retorna a conexão
    return $conn;
}
catch (PDOException $e) {
    // 1) Captura a exceção e constrói $errorMsg com a mensagem real
    $errorMsg        = "Erro na conexão com o banco de dados:\n" . $e->getMessage();
    // 2) Sinaliza que não deve mostrar menu ou botão “voltar”
    $showMenuAndBack = false;
    // 3) Ajusta o HTTP status para 500 (erro interno)
    http_response_code(500);
    // 4) Inclui o template de erro e encerra
    require_once __DIR__ . '/error.php';
    exit;
}
