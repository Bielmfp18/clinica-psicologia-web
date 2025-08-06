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
