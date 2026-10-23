<?php
/**
 * Listo - Ação de Login de Usuário
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    setFlash('warning', 'Por favor, informe seu e-mail e sua senha.');
    header('Location: ../views/login.php');
    exit;
}

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($senha, $user['senha'])) {
        setFlash('error', 'E-mail ou senha incorretos. Verifique suas credenciais.');
        header('Location: ../views/login.php');
        exit;
    }

    // Login com sucesso: registrar sessão
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nome'] = $user['nome'];
    $_SESSION['usuario_email'] = $user['email'];
    $_SESSION['usuario_avatar_cor'] = $user['avatar_cor'] ?? 'indigo';

    setFlash('success', 'Olá, ' . clean($user['nome']) . '! Bem-vindo de volta.');
    header('Location: ../views/dashboard.php');
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro durante a autenticação: ' . $e->getMessage());
    header('Location: ../views/login.php');
    exit;
}
