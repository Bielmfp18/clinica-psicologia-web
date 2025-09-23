<?php
// helpers.php
function token_hash(string $token): string {
    return hash('sha256', $token);
}

function end_of_day_datetime(): string {
    // expira à meia-noite (início do próximo dia)
    return date('Y-m-d H:i:s', strtotime('tomorrow'));
}

function cookie_expire_timestamp_end_of_day(): int {
    return strtotime('tomorrow');
}

// Gera token (hex)
function generate_token(int $bytes = 16): string {
    return bin2hex(random_bytes($bytes));
}
