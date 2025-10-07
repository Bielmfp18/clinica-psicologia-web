<?php
// helpers.php

// Gera token (hex)
function generate_token($bytes = 16) {
    return bin2hex(random_bytes($bytes));
}

// Hash do token para armazenar no DB
function token_hash($token) {
    return hash('sha256', $token); // 64 chars
}

// Expira ao final do dia (para tokens de login rápidos)
function end_of_day_datetime() {
    $t = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
    $t->setTime(23,59,59);
    return $t->format('Y-m-d H:i:s');
}

// Retorna data/hora X dias à frente (ex: 30 dias para cookie persistente)
function days_from_now_datetime(int $days = 30) {
    $t = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
    $t->modify("+$days days");
    return $t->format('Y-m-d H:i:s');
}

// Cookie para login persistente (grava o token RAW no cookie)
function set_persistent_login_cookie($token, $days = 30) {
    $expire = time() + $days * 86400;
    setcookie('login_token', $token, [
        'expires' => $expire,
        'path' => '/',
        'domain' => '', // ajuste se necessário
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Limpa cookie
function clear_persistent_login_cookie() {
    setcookie('login_token', '', time() - 3600, '/');
}

// Escape simples para saída
function esc($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
