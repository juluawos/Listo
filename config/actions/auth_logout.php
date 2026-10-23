<?php
/**
 * Listo - Ação de Logout
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';

// Limpar variáveis de sessão
$_SESSION = [];

// Destruir cookie de sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sessão
session_destroy();

// Reiniciar sessão temporária apenas para a mensagem flash
session_start();
setFlash('info', 'Você saiu da sua conta com segurança. Até breve!');

header('Location: ../views/login.php');
exit;
