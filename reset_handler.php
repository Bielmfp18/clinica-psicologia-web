<?php
// RESET HANDLER

session_name('Mente_Renovada');
session_start();

require __DIR__ . '/conn/conexao.php'; // espera $conn (PDO ou mysqli)
date_default_timezone_set('America/Sao_Paulo');

// === CONFIG ===
define('DEBUG_MODE', true); // true para desenvolvimento (mostra/loga mais detalhes). Em produção troque para false.
$logFile = __DIR__ . '/logs/reset_handler.log';

// cria pasta de logs se não existir
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}

function log_it($msg) {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    error_log($line, 3, $logFile);
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        // opcional: também loga no error_log padrão
        error_log($line);
    }
}

// Redirect helper com flash
function flash_and_redirect($type, $message, $location = 'index.php') {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    header('Location: ' . $location);
    exit;
}

// === checagem método ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Recebe e valida dados
$token = $_POST['token'] ?? '';
$senha = $_POST['senha'] ?? '';
$senha_confirm = $_POST['senha_confirm'] ?? '';

if (!is_string($token) || !preg_match('/^[0-9a-fA-F]{64}$/', $token)) {
    flash_and_redirect('danger', 'Link inválido.');
}

if (empty($senha) || strlen($senha) < 6) {
    flash_and_redirect('danger', 'A senha deve ter no mínimo 6 caracteres.', 'senha_redefinir.php?token=' . urlencode($token));
}

if ($senha !== $senha_confirm) {
    flash_and_redirect('danger', 'As senhas não coincidem.', 'senha_redefinir.php?token=' . urlencode($token));
}

// === Detecta tipo de conexão (PDO ou mysqli) ===
$isPDO = false;
$isMySQLi = false;
if (isset($conn) && $conn instanceof PDO) {
    $isPDO = true;
    // garanta que o PDO jogue exceções
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} elseif (isset($conn) && (gettype($conn) === 'object') && get_class($conn) === 'mysqli') {
    $isMySQLi = true;
} else {
    log_it("Conexão desconhecida. Verifique conn/conexao.php (esperado PDO ou mysqli).");
    flash_and_redirect('danger', 'Erro interno (DB). Contacte o administrador.');
}

try {
    // Hash do token (usando sha256 se foi assim que salvou)
    $token_hash = hash('sha256', $token);

    // Buscando token válido
    if ($isPDO) {
        $sql = "SELECT pr.id AS reset_id, pr.psicologo_id, pr.expires_at, pr.used
                FROM password_resets pr
                WHERE pr.token_hash = :th
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':th' => $token_hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else { // mysqli
        $stmt = $conn->prepare("SELECT id, psicologo_id, expires_at, used FROM password_resets WHERE token_hash = ? LIMIT 1");
        $stmt->bind_param('s', $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if ($row) {
            $row = [
                'reset_id' => $row['id'],
                'psicologo_id' => $row['psicologo_id'],
                'expires_at' => $row['expires_at'],
                'used' => $row['used']
            ];
        }
    }

    if (!$row) {
        log_it("Token não encontrado. token_hash={$token_hash}");
        flash_and_redirect('danger', 'Token inválido ou já utilizado.');
    }

    if ((int)$row['used'] === 1) {
        log_it("Token já usado. reset_id={$row['reset_id']}");
        flash_and_redirect('danger', 'Este link já foi utilizado.');
    }

    $expires = new DateTime($row['expires_at']);
    $now = new DateTime();
    if ($now > $expires) {
        log_it("Token expirado. reset_id={$row['reset_id']}, expires_at={$row['expires_at']}");
        flash_and_redirect('danger', 'Token expirado. Solicite novamente a redefinição.', 'forgot_password.php');
    }

    $psicologo_id = (int)$row['psicologo_id'];
    $password_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Atualiza em transação (PDO) ou em conjunto (mysqli)
    if ($isPDO) {
        $conn->beginTransaction();
        // Atenção: seu campo de senha no DB é 'senha' (conforme seu login), não 'password'
        $upd = $conn->prepare("UPDATE psicologo SET senha = :pw WHERE id = :id");
        $upd->execute([':pw' => $password_hash, ':id' => $psicologo_id]);

        $mark = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = :rid");
        $mark->execute([':rid' => $row['reset_id']]);

        $conn->commit();
    } else { // mysqli
        $conn->begin_transaction();
        $stmt = $conn->prepare("UPDATE psicologo SET senha = ? WHERE id = ?");
        $stmt->bind_param('si', $password_hash, $psicologo_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        $stmt->bind_param('i', $row['reset_id']);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
    }

    log_it("Senha atualizada com sucesso para psicologo_id={$psicologo_id}, reset_id={$row['reset_id']}");

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Senha alterada com sucesso. Faça login com a nova senha.'];
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    // tenta rollback se PDO
    try {
        if ($isPDO && isset($conn) && $conn->inTransaction()) $conn->rollBack();
        if ($isMySQLi && isset($conn) && $conn->connect_errno === 0) $conn->rollback();
    } catch (Exception $ex) {
        // ignora
    }

    $msg = "reset_handler exception: " . $e->getMessage();
    log_it($msg . " | trace: " . $e->getTraceAsString());

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        // em dev mostre o erro no flash (temporário)
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro interno: ' . $e->getMessage()];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro interno. Tente novamente mais tarde.'];
    }
    header('Location: index.php');
    exit;
}
