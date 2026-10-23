<?php
/**
 * Listo - Exclusão e Restauração de Item
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

$itemId = (int)($_POST['item_id'] ?? $_GET['id'] ?? 0);
$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'lixeira';

if (!$itemId) {
    setFlash('error', 'Item não especificado.');
    header('Location: ../views/lists.php');
    exit;
}

try {
    $pdo = getDB();

    $stmtItem = $pdo->prepare("SELECT id, lista_id, titulo FROM itens_lista WHERE id = ?");
    $stmtItem->execute([$itemId]);
    $item = $stmtItem->fetch();

    if (!$item) {
        setFlash('error', 'Item não encontrado.');
        header('Location: ../views/lists.php');
        exit;
    }

    $perm = getListPermissions($pdo, $item['lista_id'], $userId);
    if (!$perm || !$perm['can_view']) {
        setFlash('error', 'Você não tem permissão para alterar este item.');
        header('Location: ../views/lists.php');
        exit;
    }

    if ($acao === 'restaurar') {
        $stmt = $pdo->prepare("UPDATE itens_lista SET status = 'ativo', atualizado_em = NOW() WHERE id = ?");
        $stmt->execute([$itemId]);
        setFlash('success', 'Item "' . clean($item['titulo']) . '" restaurado!');
    } elseif ($acao === 'excluir_permanente') {
        $stmt = $pdo->prepare("DELETE FROM itens_lista WHERE id = ?");
        $stmt->execute([$itemId]);
        setFlash('info', 'Item excluído permanentemente.');
    } else {
        // Enviar para a lixeira do histórico
        $stmt = $pdo->prepare("UPDATE itens_lista SET status = 'lixeira', atualizado_em = NOW() WHERE id = ?");
        $stmt->execute([$itemId]);
        setFlash('info', 'Item movido para a lixeira do histórico.');
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? ('../views/list_view.php?id=' . $item['lista_id'])));
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao alterar item: ' . $e->getMessage());
    header('Location: ../views/lists.php');
    exit;
}
