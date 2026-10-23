<?php
/**
 * Listo - Atualização de Perfil de Usuário
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/profile.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$avatarCor = trim($_POST['avatar_cor'] ?? 'indigo');
$novaSenha = $_POST['nova_senha'] ?? '';
$senhaAtual = $_POST['senha_atual'] ?? '';

if (empty($nome) || empty($email)) {
    setFlash('error', 'Nome e e-mail não podem ficar vazios.');
    header('Location: ../views/profile.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'E-mail em formato inválido.');
    header('Location: ../views/profile.php');
    exit;
}

try {
    $pdo = getDB();

    // Checar se o e-mail já pertence a outro usuário
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmtCheck->execute([$email, $userId]);
    if ($stmtCheck->fetch()) {
        setFlash('error', 'Este e-mail já está sendo utilizado por outro usuário.');
        header('Location: ../views/profile.php');
        exit;
    }

    // Se informou nova senha, validar senha atual
    if (!empty($novaSenha)) {
        if (strlen($novaSenha) < 6) {
            setFlash('error', 'A nova senha deve ter pelo menos 6 caracteres.');
            header('Location: ../views/profile.php');
            exit;
        }

        $stmtPass = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmtPass->execute([$userId]);
        $currHash = $stmtPass->fetchColumn();

        if (!password_verify($senhaAtual, $currHash)) {
            setFlash('error', 'A sua senha atual informada está incorreta.');
            header('Location: ../views/profile.php');
            exit;
        }

        $newHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmtUpdate = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, avatar_cor = ?, senha = ? WHERE id = ?");
        $stmtUpdate->execute([$nome, $email, $avatarCor, $newHash, $userId]);
    } else {
        $stmtUpdate = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, avatar_cor = ? WHERE id = ?");
        $stmtUpdate->execute([$nome, $email, $avatarCor, $userId]);
    }

    // Atualizar dados na sessão
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['usuario_avatar_cor'] = $avatarCor;

    setFlash('success', 'Perfil atualizado com sucesso!');
    header('Location: ../views/profile.php');
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao atualizar perfil: ' . $e->getMessage());
    header('Location: ../views/profile.php');
    exit;
}
