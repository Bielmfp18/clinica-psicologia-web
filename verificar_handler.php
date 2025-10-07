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
$email_raw = trim($_POST['email'] ?? '');
$email = filter_var($email_raw, FILTER_VALIDATE_EMAIL);
$codigo = trim($_POST['codigo'] ?? '');

// Validações básicas
if (!$email || !preg_match('/^\d{6}$/', $codigo)) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Dados inválidos.'];
    header('Location: verifY_email.php?email=' . urlencode($email_raw));
    exit;
}

/*
  Compatibilidade: se a função domain_has_mx não existir no email_utils.php,
  definimos um fallback simples que usa getmxrr / checkdnsrr.
  Isso evita o erro "Undefined function".
*/
if (!function_exists('domain_has_mx')) {
    function domain_has_mx(string $email): bool {
        $at = strrpos($email, '@');
        if ($at === false) return false;
        $domain = substr($email, $at + 1);
        if (!$domain) return false;

        // tenta getmxrr (Windows pode não suportar)
        if (function_exists('getmxrr')) {
            $mxhosts = [];
            if (@getmxrr($domain, $mxhosts) && !empty($mxhosts)) {
                return true;
            }
        }

        // fallback para checkdnsrr (verifica MX ou A)
        if (function_exists('checkdnsrr')) {
            if (@checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A')) {
                return true;
            }
        }

        // sem métodos disponíveis ou sem registro MX/A encontrado
        return false;
    }
}

// Opcional: checar domínio — se quiser manter, ok; em dev pode comentar
if (!domain_has_mx($email)) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Domínio de e-mail inválido.'];
    header('Location: verifY_email.php?email=' . urlencode($email));
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

    // Se já não houver código de verificação (NULL/empty), consideramos conta já verificada
    if (empty($row['verification_code_hash'])) {
        $_SESSION['flash'] = ['type' => 'info', 'message' => 'Conta já verificada. Faça login.'];
        header('Location: index.php?login=1');
        exit;
    }

    // tentativas
    $attempts = (int)$row['verification_attempts'];
    if ($attempts >= 5) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Muitas tentativas inválidas. Solicite reenvio do código.'];
        header('Location: verifY_email.php?email=' . urlencode($email));
        exit;
    }

    // expiração — comparação robusta usando strtotime
    $expires = $row['verification_expires'];
    if ($expires === null || strtotime($expires) < time()) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código expirado. Solicite reenvio.'];
        header('Location: verifY_email.php?email=' . urlencode($email));
        exit;
    }

    // valida código (assumindo que você gravou hash com password_hash)
    $hash = $row['verification_code_hash'] ?? null;
    if ($hash !== null && password_verify($codigo, $hash)) {
        // marca como verificado: removemos o código e resetamos tentativas
        $upd = $conn->prepare("
            UPDATE psicologo
            SET ativo = 1,
                verification_code_hash = NULL,
                verification_expires = NULL,
                verification_attempts = 0,
                verification_sent_at = NULL,
                data_atualizacao = NOW()
            WHERE id = :id
            LIMIT 1
        ");
        $upd->bindParam(':id', $row['id'], PDO::PARAM_INT);
        $upd->execute();

        // faz login automático
        $_SESSION['login_admin'] = $email;
        $_SESSION['psicologo_id'] = (int)$row['id'];
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Conta verificada e logada com sucesso!'];
        header('Location: index.php');
        exit;
    } else {
        // incrementa tentativas (mantive sua lógica)
        $inc = $conn->prepare("UPDATE psicologo SET verification_attempts = verification_attempts + 1 WHERE id = :id LIMIT 1");
        $inc->bindParam(':id', $row['id'], PDO::PARAM_INT);
        $inc->execute();

        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código inválido.'];
        header('Location: verifY_email.php?email=' . urlencode($email));
        exit;
    }
} catch (Exception $e) {
    error_log('verificar_handler error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro no servidor. Tente novamente mais tarde.'];
    header('Location: index.php?Cadastro=1');
    exit;
}
