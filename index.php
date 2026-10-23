<?php
/**
 * Listo - Ponto de Entrada Principal
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: views/dashboard.php');
} else {
    header('Location: views/login.php');
}
exit;
