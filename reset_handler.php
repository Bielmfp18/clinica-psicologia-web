<?php
// reset_handler.php
require_once __DIR__ . '/conn/conexao.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('Mente_Renovada');
    session_start();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Requisição inválida.');
    }

    $token = $_POST['t'] ?? '';
    $uid = $_POST['u'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';

    if (empty($token) || empty($uid) || empty($senha) || empty($senha2)) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Preencha todos os campos.'];
        header('Location: reset_password.php?t=' . urlencode($token) . '&u=' . urlencode($uid));
        exit;
    }

    if ($senha !== $senha2) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'As senhas não conferem.'];
        header('Location: reset_password.php?t=' . urlencode($token) . '&u=' . urlencode($uid));
        exit;
    }

    if (strlen($senha) < 8) {
        $_SESSION['flash'] = ['type' => 'warning', 'message' => 'A senha deve ter ao menos 8 caracteres.'];
        header('Location: reset_password.php?t=' . urlencode($token) . '&u=' . urlencode($uid));
        exit;
    }

    $token_h = token_hash($token);
    $psicologo_id = (int)$uid;

    // Buscar o token válido
    $sel = $conn->prepare("
        SELECT pr.id AS reset_id, pr.expires_at, pr.used, p.id AS pid
        FROM password_resets pr
        JOIN psicologo p ON p.id = pr.psicologo_id
        WHERE pr.token_hash = :h
          AND pr.psicologo_id = :pid
        LIMIT 1
    ");
    $sel->bindParam(':h', $token_h);
    $sel->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
    $sel->execute();
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Link inválido ou já utilizado.'];
        header('Location: forgot_password.php');
        exit;
    }

    if ((int)$row['used'] === 1) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Este link já foi utilizado.'];
        header('Location: forgot_password.php');
        exit;
    }

    if (strtotime($row['expires_at']) < time()) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'O link expirou. Solicite nova recuperação.'];
        header('Location: forgot_password.php');
        exit;
    }

    //  Atualiza senha do psicólogo
    $newHash = password_hash($senha, PASSWORD_DEFAULT);
    $upd = $conn->prepare("UPDATE psicologo SET senha = :senha, data_atualizacao = NOW() WHERE id = :id");
    $upd->bindParam(':senha', $newHash);
    $upd->bindParam(':id', $psicologo_id, PDO::PARAM_INT);
    $upd->execute();

    // Marca o token como usado
    $mark = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = :rid");
    $mark->bindParam(':rid', $row['reset_id'], PDO::PARAM_INT);
    $mark->execute();

    // Opcional: invalidar outros tokens do usuário
    $conn->prepare("UPDATE password_resets SET used = 1 WHERE psicologo_id = :pid AND id != :rid")
         ->execute([':pid' => $psicologo_id, ':rid' => $row['reset_id']]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Senha atualizada com sucesso. Faça login com a nova senha.'];
    header('Location: index.php?login=1');
    exit;

} catch (Exception $e) {
    error_log("reset_handler error: " . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro ao redefinir senha. Tente novamente.'];
    header('Location: forgot_password.php');
    exit;
}
