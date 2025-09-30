<?php
// EMAIL UTILS

/**
 * Valida formato básico do e-mail.
 */
function is_valid_email_format(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Verifica se o domínio tem registro MX ou (fallback) A/AAAA.
 */
function domain_has_mx(string $email): bool {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return false;
    $domain = $parts[1];

    // getmxrr() retorna hosts MX se disponíveis
    if (function_exists('getmxrr')) {
        $mxhosts = [];
        if (getmxrr($domain, $mxhosts) && count($mxhosts) > 0) return true;
    }

    // fallback para checar A ou AAAA (alguns ambientes windows não têm getmxrr)
    if (checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA')) return true;

    return false;
}

/**
 * Opcional: tentativa de verificação SMTP (RCPT TO).
 * Atenção: muitos servidores ignoram ou bloqueiam esse tipo de verificação.
 * Retorna true se parece existir, false em caso de não existir ou erro.
 */
function smtp_check(string $email, string $from = 'no-reply@seudominio.com', int $timeout = 5): bool {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return false;
    $domain = $parts[1];

    // obtém MX
    $mxhosts = [];
    if (function_exists('getmxrr') && getmxrr($domain, $mxhosts)) {
        // ord. mx já vem por prioridade
    } else {
        // fallback: usar o próprio domínio
        $mxhosts = [$domain];
    }

    foreach ($mxhosts as $mx) {
        // tentar conexão na porta 25
        $fp = @fsockopen($mx, 25, $errno, $errstr, $timeout);
        if (!$fp) continue;

        stream_set_timeout($fp, $timeout);

        $res = fgets($fp); // banner
        if ($res === false) { fclose($fp); continue; }

        // EHLO
        fputs($fp, "EHLO " . gethostname() . "\r\n");
        $res = fgets($fp);
        // pular possíveis linhas de resposta
        while ($res !== false && substr($res,3,1) === '-') { $res = fgets($fp); }

        // MAIL FROM
        fputs($fp, "MAIL FROM:<{$from}>\r\n");
        $res = fgets($fp);
        if ($res === false || (int)substr($res,0,3) >= 400) { fclose($fp); continue; }

        // RCPT TO
        fputs($fp, "RCPT TO:<{$email}>\r\n");
        $res = fgets($fp);
        fclose($fp);

        if ($res !== false) {
            $code = (int)substr($res,0,3);
            // 250 / 251 = OK, 550 = nonexistent, outros -> inconclusivo
            if ($code === 250 || $code === 251) return true;
            if ($code >= 500 && $code < 600) return false;
        }
    }

    // inconclusivo -> retornar false (ou true, se quiser permissivo). 
    return false;
}

/**
 * Checa se o e-mail existe na tabela psicologo.
 */
function email_exists_in_db(PDO $conn, string $email): bool {
    $stmt = $conn->prepare("SELECT id FROM psicologo WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($row !== false);
}
