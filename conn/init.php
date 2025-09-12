<?php
// init.php
declare(strict_types=1);

// Map de textos padrão de status HTTP (só para referência, não vamos exibir esses)
$statusTexts = [
  400 => 'Bad Request',
  401 => 'Unauthorized',
  403 => 'Forbidden',
  404 => 'Not Found',
  500 => 'Internal Server Error',
];

// Exception handler global
set_exception_handler(function(Throwable $e) {
    // Sempre logamos o erro real para diagnóstico
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());

    // Se o catch de conexão (ou outro catch manual) já definiu uma mensagem,
    // usamos ela; senão, caímos na genérica:
    $msg = $GLOBALS['errorMsg']
         ?? 'Ocorreu um erro. Por favor, tente novamente mais tarde.';

    // Se alguém já definiu um código de status (ex: 500 no catch de conexão)
    // usamos ele; senão, tentamos usar o código da exception (se for um 4xx/5xx válido)
    $code = isset($GLOBALS['errorCode'])
          ? (int)$GLOBALS['errorCode']
          : ( ($e->getCode() >= 400 && $e->getCode() < 600)
              ? (int)$e->getCode()
              : 500 );

    // Se quiser sobrescrever também a exibição de botões:
    $showMenuAndBack = $GLOBALS['showMenuAndBack'] ?? true;

    // Passa para o ERROR.php
    $GLOBALS['errorCode']       = $code;
    $GLOBALS['errorMsg']        = $msg;
    $GLOBALS['showMenuAndBack'] = $showMenuAndBack;

    http_response_code($code);
    require __DIR__ . '/ERROR.php';
    exit;
});

// Shutdown handler para erros fatais
register_shutdown_function(function() {
    $err = error_get_last();
    if (!$err) return;

    $fatals = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (! in_array($err['type'], $fatals, true)) {
        return;
    }

    // Log
    error_log("FATAL: {$err['message']} em {$err['file']}:{$err['line']}");

    // Mesma lógica: mensagem genérica sempre
    $msg = 'Ocorreu um erro. Por favor, tente novamente mais tarde.';
    $code = 500;
    $showMenuAndBack = true;

    $GLOBALS['errorCode']       = $code;
    $GLOBALS['errorMsg']        = $msg;
    $GLOBALS['showMenuAndBack'] = $showMenuAndBack;

    http_response_code($code);
    require __DIR__ . '/ERROR.php';
});

if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    function base64url_decode($data) {
        $pad = 4 - (strlen($data) % 4);
        if ($pad < 4) $data .= str_repeat('=', $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

if (!function_exists('encode_id_portable')) {
    if (!defined('ID_XOR_KEY')) define('ID_XOR_KEY', "k3y1"); // ajuste se quiser
    function encode_id_portable(int $id): string {
        $bin = pack('N', $id);
        $xored = $bin ^ ID_XOR_KEY;
        return base64url_encode($xored);
    }
    function decode_id_portable(string $token) {
        $data = base64url_decode($token);
        if ($data === false || strlen($data) !== 4) return false;
        $bin = $data ^ ID_XOR_KEY;
        $arr = unpack('N', $bin);
        return isset($arr[1]) ? (int)$arr[1] : false;
    }
}