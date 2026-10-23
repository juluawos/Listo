<?php
/**
 * Listo - Edição de Lista
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
$descricao = trim($_POST['descricao'] ?? '');
$tipo = trim($_POST['tipo'] ?? 'tarefas');
$cor = trim($_POST['cor'] ?? 'indigo');
$grupoId = !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : null;

if (!$listaId || empty($titulo)) {
    setFlash('warning', 'Dados incompletos para edição da lista.');
    header('Location: ../views/lists.php');
    exit;
}

try {
    $pdo = getDB();
    $perm = getListPermissions($pdo, $listaId, $userId);

    if (!$perm || !$perm['can_edit']) {
        setFlash('error', 'Você não tem permissão para editar esta lista.');
        header('Location: ../views/lists.php');
        exit;
    }

    if ($grupoId) {
        $stmtGrupo = $pdo->prepare("SELECT id FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
        $stmtGrupo->execute([$grupoId, $userId]);
        if (!$stmtGrupo->fetch()) {
            setFlash('error', 'Você não tem permissão para associar a este grupo.');
            header('Location: ../views/list_view.php?id=' . $listaId);
            exit;
        }
    }

    $icone = $_POST['icone'] ?? $perm['list']['icone'];

    $stmt = $pdo->prepare("
        UPDATE listas 
        SET titulo = ?, descricao = ?, tipo = ?, icone = ?, cor = ?, grupo_id = ?, atualizado_em = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$titulo, $descricao, $tipo, $icone, $cor, $grupoId, $listaId]);

    setFlash('success', 'Lista atualizada com sucesso!');
    header('Location: ../views/list_view.php?id=' . $listaId);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao atualizar lista: ' . $e->getMessage());
    header('Location: ../views/list_view.php?id=' . $listaId);
    exit;
}
