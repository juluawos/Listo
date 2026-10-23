<?php
/**
 * Listo - Exclusão de Grupo
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

$grupoId = (int)($_POST['grupo_id'] ?? 0);

if (!$grupoId) {
    setFlash('error', 'Grupo não especificado.');
    header('Location: ../views/groups.php');
    exit;
}

try {
    $pdo = getDB();

    $stmtGrupo = $pdo->prepare("SELECT id, nome, criado_por FROM grupos WHERE id = ?");
    $stmtGrupo->execute([$grupoId]);
    $grupo = $stmtGrupo->fetch();

    if (!$grupo) {
        setFlash('error', 'Grupo não encontrado.');
        header('Location: ../views/groups.php');
        exit;
    }

    $stmtRole = $pdo->prepare("SELECT papel FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
    $stmtRole->execute([$grupoId, $userId]);
    $membership = $stmtRole->fetch();

    $isAdmin = ($membership && $membership['papel'] === 'admin') || ((int)$grupo['criado_por'] === (int)$userId);

    if (!$isAdmin) {
        setFlash('error', 'Apenas o administrador pode excluir o grupo.');
        header('Location: ../views/group_view.php?id=' . $grupoId);
        exit;
    }

    // Excluir grupo
    $stmtDel = $pdo->prepare("DELETE FROM grupos WHERE id = ?");
    $stmtDel->execute([$grupoId]);

    setFlash('info', 'O grupo "' . clean($grupo['nome']) . '" foi excluído com sucesso.');
    header('Location: ../views/groups.php');
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao excluir grupo: ' . $e->getMessage());
    header('Location: ../views/group_view.php?id=' . $grupoId);
    exit;
}
