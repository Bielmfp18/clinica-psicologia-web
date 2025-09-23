<?php
// logout.php
// Logout que NÃO destrói o cookie persistente "login_token".
// Uso: redirecionar o usuário aqui quando ele clicar em "Sair".
// Observação: manter o cookie persistente permite "lembrar este dispositivo"
// e pular a verificação por e-mail na próxima vez (se o token for válido).

// garante o mesmo nome de sessão usado em todo o app
session_name('Mente_Renovada');
session_start();

// CONSTRUIR O FLASH (mensagem para o usuário)
$flash = [
    'type'    => 'danger',
    'message' => 'Você se desconectou da sua conta!'
];

// -------------------------------------------------
// 1) Guardamos o flash numa variável local para que
//    possamos restaurá-lo após destruir a sessão.
// -------------------------------------------------
$tempFlash = $flash;

// -------------------------------------------------
// 2) Remove apenas os dados de autenticação da sessão
//    (não remove o cookie persistente 'login_token').
// -------------------------------------------------
unset($_SESSION['login_admin'], $_SESSION['psicologo_id']);

// se quiser, limpe outras chaves sensíveis:
// unset($_SESSION['some_other_sensitive_key']);

// -------------------------------------------------
// 3) Opcional: Limpeza e destruição da sessão atual
//    — removemos dados do lado do servidor para segurança.
//    Isso NÃO afeta o cookie 'login_token' (não o removemos).
// -------------------------------------------------
$params = session_get_cookie_params();

// (a) limpa o array de sessão
$_SESSION = [];

// (b) destrói a sessão no servidor
session_destroy();

// (c) remove o cookie de sessão (cookie de sessão PHP) para forçar nova sessão
//     OBS: isso é separado do cookie 'login_token' e é seguro fazê-lo.
setcookie(
    session_name(),
    '',
    time() - 42000,
    $params['path'] ?? '/',
    $params['domain'] ?? '',
    $params['secure'] ?? false,
    $params['httponly'] ?? true
);

// -------------------------------------------------
// 4) Reinicia sessão apenas para manter o flash disponível
//    na próxima página (index.php). Assim o index pode exibir a mensagem.
// -------------------------------------------------
session_name('Mente_Renovada');
session_start();
$_SESSION['flash'] = $tempFlash;

// -------------------------------------------------
// 5) NÃO remover ou alterar o cookie "login_token" aqui.
//    Se quiser oferecer opção para "Esquecer este dispositivo"
//    crie um logout_forget.php que remova o cookie e invalide o token no DB.
// -------------------------------------------------

// Redireciona para a página inicial (ou onde preferir)
header('Location: index.php');
exit;
