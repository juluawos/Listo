<?php
/**
 * Listo - Convidar Usuário para o Grupo por E-mail
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/groups.php');
    exit;
}

$grupoId = (int)($_POST['grupo_id'] ?? 0);
$email = strtolower(trim($_POST['email'] ?? ''));

if (!$grupoId || empty($email)) {
    setFlash('warning', 'Informe o grupo e o e-mail do usuário.');
    header('Location: ../views/groups.php');
    exit;
}

try {
    $pdo = getDB();

    // Checar se o usuário solicitante é admin ou membro com permissão
    $stmtMembro = $pdo->prepare("SELECT papel FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
    $stmtMembro->execute([$grupoId, $userId]);
    $membro = $stmtMembro->fetch();

    if (!$membro) {
        setFlash('error', 'Você não pertence a este grupo.');
        header('Location: ../views/groups.php');
        exit;
    }

    // Buscar usuário pelo e-mail
    $stmtUser = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
    $stmtUser->execute([$email]);
    $convidado = $stmtUser->fetch();

    if (!$convidado) {
        setFlash('warning', 'Nenhum usuário cadastrado encontrado com o e-mail "' . clean($email) . '". Peça para a pessoa se cadastrar primeiro ou passe o código de convite.');
        header('Location: ../views/group_view.php?id=' . $grupoId);
        exit;
    }

    // Verificar se já é membro do grupo
    $stmtJaMembro = $pdo->prepare("SELECT id FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
    $stmtJaMembro->execute([$grupoId, $convidado['id']]);
    if ($stmtJaMembro->fetch()) {
        setFlash('info', clean($convidado['nome']) . ' já faz parte deste grupo!');
        header('Location: ../views/group_view.php?id=' . $grupoId);
        exit;
    }

    // Adicionar membro
    $stmtAdd = $pdo->prepare("
        INSERT INTO membros_grupo (grupo_id, usuario_id, papel, entrou_em)
        VALUES (?, ?, 'membro', NOW())
    ");
    $stmtAdd->execute([$grupoId, $convidado['id']]);

    setFlash('success', clean($convidado['nome']) . ' foi adicionado(a) ao grupo com sucesso!');
    header('Location: ../views/group_view.php?id=' . $grupoId);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao convidar usuário: ' . $e->getMessage());
    header('Location: ../views/group_view.php?id=' . $grupoId);
    exit;
}
