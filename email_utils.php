<?php
// EMAILS UTILS

// Verifica se o domínio do e-mail tem registro MX válido
function domain_has_mx(string $email): bool {
    $at = strrpos($email, '@');
    if ($at === false) return false;

    $domain = substr($email, $at + 1);
    if (!$domain) return false;

    // tenta verificar via MX
    if (function_exists('getmxrr')) {
        $mxhosts = [];
        if (@getmxrr($domain, $mxhosts) && !empty($mxhosts)) {
            return true;
        }
    }

    // fallback com checkdnsrr
    if (function_exists('checkdnsrr')) {
        if (@checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A')) {
            return true;
        }
    }

    return false;
}
