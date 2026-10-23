<?php
/**
 * Listo - Alternar Status do Item (Concluído / Pendente)
 * Sincronização imediata no Banco de Dados
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

$itemId = (int)($_POST['item_id'] ?? 0);
$novoStatus = isset($_POST['concluido']) ? (int)$_POST['concluido'] : null;

if (!$itemId) {
    if ($isAjax) {
        jsonResponse(['success' => false, 'message' => 'Item não especificado.'], 400);
    }
    setFlash('error', 'Item não especificado.');
    header('Location: ../views/lists.php');
    exit;
}

try {
    $pdo = getDB();

    // Buscar lista do item para checar permissão
    $stmtItem = $pdo->prepare("SELECT id, lista_id, concluido, titulo FROM itens_lista WHERE id = ?");
    $stmtItem->execute([$itemId]);
    $item = $stmtItem->fetch();

    if (!$item) {
        if ($isAjax) {
            jsonResponse(['success' => false, 'message' => 'Item não encontrado.'], 404);
        }
        setFlash('error', 'Item não encontrado.');
        header('Location: ../views/lists.php');
        exit;
    }

    $perm = getListPermissions($pdo, $item['lista_id'], $userId);
    if (!$perm || !$perm['can_view']) {
        if ($isAjax) {
            jsonResponse(['success' => false, 'message' => 'Permissão negada.'], 403);
        }
        setFlash('error', 'Permissão negada.');
        header('Location: ../views/lists.php');
        exit;
    }

    // Se o novo status não foi explicitamente passado, inverter o atual
    if ($novoStatus === null) {
        $novoStatus = $item['concluido'] ? 0 : 1;
    } else {
        $novoStatus = $novoStatus ? 1 : 0;
    }

    // Se for supermercado e marcou como comprado, pode salvar o preco_real se informado
    $extraSql = "";
    $params = [$novoStatus];
    if (isset($_POST['preco_real']) && is_numeric($_POST['preco_real'])) {
        $extraSql = ", preco_real = ?";
        $params[] = (float)$_POST['preco_real'];
    }
    $params[] = $itemId;

    $stmtUpdate = $pdo->prepare("UPDATE itens_lista SET concluido = ? {$extraSql}, atualizado_em = NOW() WHERE id = ?");
    $stmtUpdate->execute($params);

    // Atualizar timestamp da lista
    $stmtUpList = $pdo->prepare("UPDATE listas SET atualizado_em = NOW() WHERE id = ?");
    $stmtUpList->execute([$item['lista_id']]);

    $msg = $novoStatus ? 'Item "' . clean($item['titulo']) . '" marcado como concluído!' : 'Item "' . clean($item['titulo']) . '" reaberto.';

    if ($isAjax) {
        jsonResponse([
            'success' => true,
            'message' => $msg,
            'concluido' => $novoStatus,
            'item_id' => $itemId
        ]);
    }

    setFlash('success', $msg);
    header('Location: ../views/list_view.php?id=' . $item['lista_id']);
    exit;

} catch (Exception $e) {
    if ($isAjax) {
        jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
    }
    setFlash('error', 'Erro ao atualizar item: ' . $e->getMessage());
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/lists.php'));
    exit;
}
