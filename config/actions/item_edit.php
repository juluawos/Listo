<?php
/**
 * Listo - Edição de Item
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/lists.php');
    exit;
}

$itemId = (int)($_POST['item_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');

if (!$itemId || empty($titulo)) {
    setFlash('warning', 'Dados incompletos para atualizar o item.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/lists.php'));
    exit;
}

try {
    $pdo = getDB();

    $stmtItem = $pdo->prepare("SELECT id, lista_id FROM itens_lista WHERE id = ?");
    $stmtItem->execute([$itemId]);
    $item = $stmtItem->fetch();

    if (!$item) {
        setFlash('error', 'Item não encontrado.');
        header('Location: ../views/lists.php');
        exit;
    }

    $perm = getListPermissions($pdo, $item['lista_id'], $userId);
    if (!$perm || !$perm['can_view']) {
        setFlash('error', 'Você não tem permissão para editar este item.');
        header('Location: ../views/lists.php');
        exit;
    }

    $quantidade = isset($_POST['quantidade']) && is_numeric($_POST['quantidade']) ? (float)$_POST['quantidade'] : 1.0;
    $unidade = trim($_POST['unidade'] ?? 'un');
    $precoEstimado = isset($_POST['preco_estimado']) && is_numeric($_POST['preco_estimado']) ? (float)$_POST['preco_estimado'] : 0.0;
    $precoReal = isset($_POST['preco_real']) && is_numeric($_POST['preco_real']) ? (float)$_POST['preco_real'] : 0.0;

    $genero = trim($_POST['genero'] ?? null);
    $plataforma = trim($_POST['plataforma'] ?? null);
    $nota = !empty($_POST['nota']) ? (int)$_POST['nota'] : null;

    $rendimento = trim($_POST['rendimento'] ?? null);
    $tempoPreparo = trim($_POST['tempo_preparo'] ?? null);
    $ingredientes = trim($_POST['ingredientes'] ?? null);
    $modoPreparo = trim($_POST['modo_preparo'] ?? null);

    $prioridade = trim($_POST['prioridade'] ?? 'media');
    $dataLimite = !empty($_POST['data_limite']) ? $_POST['data_limite'] : null;
    $notas = trim($_POST['notas'] ?? null);

    $stmt = $pdo->prepare("
        UPDATE itens_lista SET 
            titulo = ?,
            quantidade = ?, unidade = ?, preco_estimado = ?, preco_real = ?,
            genero = ?, plataforma = ?, nota = ?,
            rendimento = ?, tempo_preparo = ?, ingredientes = ?, modo_preparo = ?,
            prioridade = ?, data_limite = ?, notas = ?,
            atualizado_em = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $titulo,
        $quantidade, $unidade, $precoEstimado, $precoReal,
        $genero, $plataforma, $nota,
        $rendimento, $tempoPreparo, $ingredientes, $modoPreparo,
        $prioridade, $dataLimite, $notas,
        $itemId
    ]);

    setFlash('success', 'Item atualizado com sucesso!');
    header('Location: ../views/list_view.php?id=' . $item['lista_id']);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao salvar alterações do item: ' . $e->getMessage());
    header('Location: ../views/list_view.php?id=' . ($item['lista_id'] ?? ''));
    exit;
}
