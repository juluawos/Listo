<?php
/**
 * Listo - Adicionar Item à Lista
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

$listaId = (int)($_POST['lista_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');

if (!$listaId || empty($titulo)) {
    setFlash('warning', 'O título ou nome do item é obrigatório.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/lists.php'));
    exit;
}

try {
    $pdo = getDB();
    $perm = getListPermissions($pdo, $listaId, $userId);

    if (!$perm || !$perm['can_view']) {
        setFlash('error', 'Você não tem permissão para adicionar itens nesta lista.');
        header('Location: ../views/lists.php');
        exit;
    }

    $tipo = $perm['list']['tipo'];

    // Campos específicos para cada tipo de lista
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
        INSERT INTO itens_lista (
            lista_id, titulo, concluido, ordem,
            quantidade, unidade, preco_estimado, preco_real,
            genero, plataforma, nota,
            rendimento, tempo_preparo, ingredientes, modo_preparo,
            prioridade, data_limite, notas,
            criado_por, criado_em
        ) VALUES (
            ?, ?, 0, 0,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, NOW()
        )
    ");

    $stmt->execute([
        $listaId, $titulo,
        $quantidade, $unidade, $precoEstimado, $precoReal,
        $genero, $plataforma, $nota,
        $rendimento, $tempoPreparo, $ingredientes, $modoPreparo,
        $prioridade, $dataLimite, $notas,
        $userId
    ]);

    // Atualizar data da lista
    $stmtUp = $pdo->prepare("UPDATE listas SET atualizado_em = NOW() WHERE id = ?");
    $stmtUp->execute([$listaId]);

    setFlash('success', 'Item "' . clean($titulo) . '" adicionado!');
    header('Location: ../views/list_view.php?id=' . $listaId);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao adicionar item: ' . $e->getMessage());
    header('Location: ../views/list_view.php?id=' . $listaId);
    exit;
}
