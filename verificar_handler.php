<?php
// VERIFICAR HANDLER

session_name('Mente_Renovada');
session_start();

require_once 'conn/conexao.php';
require_once 'email_utils.php';

// garante que é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Recebe e sanitiza
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$codigo = trim($_POST['codigo'] ?? '');

// Validações básicas
if (!$email || !preg_match('/^\d{6}$/', $codigo)) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Dados inválidos.'];
    header('Location: verificar_email.php?email=' . urlencode($_POST['email'] ?? ''));
    exit;
}

// Opcional: checar domínio — se quiser manter, ok; em dev pode comentar
if (!domain_has_mx($email)) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Domínio de e-mail inválido.'];
    header('Location: verificar_email.php?email=' . urlencode($email));
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, nome, verification_code_hash, verification_expires, verification_attempts, ativo FROM psicologo WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'E-mail não encontrado.'];
        header('Location: index.php');
        exit;
    }

    if ((int)$row['ativo'] === 1) {
        $_SESSION['flash'] = ['type' => 'info', 'message' => 'Conta já confirmada. Faça login.'];
        header('Location: index.php?login=1');
        exit;
    }

    // tentativas
    $attempts = (int)$row['verification_attempts'];
    if ($attempts >= 5) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Muitas tentativas inválidas. Solicite reenvio do código.'];
        header('Location: verificar_email.php?email=' . urlencode($email));
        exit;
    }

    // expiração
    $expires = $row['verification_expires'];
    if ($expires === null || new DateTime() > new DateTime($expires)) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código expirado. Solicite reenvio.'];
        header('Location: verificar_email.php?email=' . urlencode($email));
        exit;
    }

    // valida código
    $hash = $row['verification_code_hash'] ?? null;
    if ($hash !== null && password_verify($codigo, $hash)) {
        $upd = $conn->prepare("
            UPDATE psicologo
            SET ativo = 1,
                verification_code_hash = NULL,
                verification_expires = NULL,
                verification_attempts = 0
            WHERE id = :id
            LIMIT 1
        ");
        $upd->bindParam(':id', $row['id'], PDO::PARAM_INT);
        $upd->execute();

        $_SESSION['login_admin'] = $email;
        $_SESSION['psicologo_id'] = (int)$row['id'];
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Conta verificada e logada com sucesso!'];
        header('Location: index.php');
        exit;
    } else {
        // incrementa tentativas
        $inc = $conn->prepare("UPDATE psicologo SET verification_attempts = verification_attempts + 1 WHERE id = :id LIMIT 1");
        $inc->bindParam(':id', $row['id'], PDO::PARAM_INT);
        $inc->execute();

        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código inválido.'];
        header('Location: verificar_email.php?email=' . urlencode($email));
        exit;
    }
} catch (Exception $e) {
    error_log('verificar_handler error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro no servidor. Tente novamente mais tarde.'];
    header('Location: index.php?Cadastro=1');
    exit;
}
