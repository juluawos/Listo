<?php
/**
 * Listo - Exclusão e Restauração de Listas (Lixeira e Histórico)
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

$listaId = (int)($_POST['lista_id'] ?? $_GET['id'] ?? 0);
$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'lixeira';

if (!$listaId) {
    setFlash('error', 'Lista não informada.');
    header('Location: ../views/lists.php');
    exit;
}

try {
    $pdo = getDB();
    $perm = getListPermissions($pdo, $listaId, $userId);

    if (!$perm || !$perm['can_delete']) {
        setFlash('error', 'Apenas o criador da lista ou administrador do grupo pode excluir esta lista.');
        header('Location: ../views/lists.php');
        exit;
    }

    if ($acao === 'restaurar') {
        $stmt = $pdo->prepare("UPDATE listas SET status = 'ativa', atualizado_em = NOW() WHERE id = ?");
        $stmt->execute([$listaId]);
        setFlash('success', 'Lista restaurada com sucesso!');
        header('Location: ../views/list_view.php?id=' . $listaId);
        exit;
    } elseif ($acao === 'excluir_permanente') {
        $stmt = $pdo->prepare("DELETE FROM listas WHERE id = ?");
        $stmt->execute([$listaId]);
        setFlash('success', 'Lista excluída permanentemente.');
        header('Location: ../views/history.php');
        exit;
    } else {
        // Mover para a Lixeira (Soft delete)
        $stmt = $pdo->prepare("UPDATE listas SET status = 'lixeira', atualizado_em = NOW() WHERE id = ?");
        $stmt->execute([$listaId]);
        setFlash('info', 'Lista enviada para a Lixeira. Você pode restaurá-la a qualquer momento em "Histórico & Lixeira".');
        header('Location: ../views/lists.php');
        exit;
    }

} catch (Exception $e) {
    setFlash('error', 'Erro ao alterar status da lista: ' . $e->getMessage());
    header('Location: ../views/lists.php');
    exit;
}
