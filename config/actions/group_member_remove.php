<?php
/**
 * Listo - Remover Membro ou Sair do Grupo
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

$grupoId = (int)($_POST['grupo_id'] ?? 0);
$alvoUsuarioId = (int)($_POST['usuario_id'] ?? 0);

if (!$grupoId || !$alvoUsuarioId) {
    setFlash('error', 'Parâmetros inválidos.');
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

    $stmtMyRole = $pdo->prepare("SELECT papel FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
    $stmtMyRole->execute([$grupoId, $userId]);
    $myMembership = $stmtMyRole->fetch();

    if (!$myMembership) {
        setFlash('error', 'Você não pertence a este grupo.');
        header('Location: ../views/groups.php');
        exit;
    }

    $isAdmin = ($myMembership['papel'] === 'admin' || (int)$grupo['criado_por'] === (int)$userId);
    $isSelf = ($alvoUsuarioId === $userId);

    // Se estiver removendo outra pessoa, exige ser admin
    if (!$isSelf && !$isAdmin) {
        setFlash('error', 'Apenas administradores podem remover outros membros.');
        header('Location: ../views/group_view.php?id=' . $grupoId);
        exit;
    }

    // Não permitir que o criador principal saia sem excluir o grupo
    if ($isSelf && (int)$grupo['criado_por'] === (int)$userId) {
        setFlash('warning', 'Você é o criador do grupo. Para sair dele, você pode excluí-lo.');
        header('Location: ../views/group_view.php?id=' . $grupoId);
        exit;
    }

    // Remover membro
    $stmtDel = $pdo->prepare("DELETE FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
    $stmtDel->execute([$grupoId, $alvoUsuarioId]);

    if ($isSelf) {
        setFlash('info', 'Você saiu do grupo "' . clean($grupo['nome']) . '".');
        header('Location: ../views/groups.php');
        exit;
    } else {
        setFlash('success', 'Membro removido do grupo com sucesso.');
        header('Location: ../views/group_view.php?id=' . $grupoId);
        exit;
    }

} catch (Exception $e) {
    setFlash('error', 'Erro ao processar remoção: ' . $e->getMessage());
    header('Location: ../views/group_view.php?id=' . $grupoId);
    exit;
}
