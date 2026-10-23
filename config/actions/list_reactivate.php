<?php
/**
 * Listo - Reativar Lista (Desmarcar todos os itens para reutilização rápida)
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

$listaId = (int)($_POST['lista_id'] ?? 0);

if (!$listaId) {
    setFlash('error', 'Lista não encontrada.');
    header('Location: ../views/lists.php');
    exit;
}

try {
    $pdo = getDB();
    $perm = getListPermissions($pdo, $listaId, $userId);

    if (!$perm || !$perm['can_edit']) {
        setFlash('error', 'Você não tem permissão para modificar esta lista.');
        header('Location: ../views/lists.php');
        exit;
    }

    // Desmarcar todos os itens e reativar a lista se estiver arquivada
    $stmt = $pdo->prepare("UPDATE itens_lista SET concluido = 0, atualizado_em = NOW() WHERE lista_id = ?");
    $stmt->execute([$listaId]);

    $stmtList = $pdo->prepare("UPDATE listas SET status = 'ativa', atualizado_em = NOW() WHERE id = ?");
    $stmtList->execute([$listaId]);

    setFlash('success', 'Lista reativada com sucesso! Todos os itens foram desmarcados e estão prontos para reutilização.');
    header('Location: ../views/list_view.php?id=' . $listaId);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao reativar lista: ' . $e->getMessage());
    header('Location: ../views/list_view.php?id=' . $listaId);
    exit;
}
