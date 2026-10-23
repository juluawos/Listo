<?php
/**
 * Listo - Criação de Lista
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

$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$tipo = trim($_POST['tipo'] ?? 'tarefas');
$cor = trim($_POST['cor'] ?? 'indigo');
$grupoId = !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : null;

if (empty($titulo)) {
    setFlash('warning', 'O título da lista é obrigatório.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../views/lists.php'));
    exit;
}

$tiposValidos = ['supermercado', 'filmes', 'receitas', 'tarefas', 'viagens', 'outros'];
if (!in_array($tipo, $tiposValidos)) {
    $tipo = 'tarefas';
}

// Ícones padrão por tipo
$iconesPadrao = [
    'supermercado' => 'shopping-cart',
    'filmes' => 'film',
    'receitas' => 'utensils',
    'tarefas' => 'check-square',
    'viagens' => 'plane',
    'outros' => 'list'
];
$icone = $_POST['icone'] ?? ($iconesPadrao[$tipo] ?? 'list');

try {
    $pdo = getDB();

    // Se vinculou a um grupo, certificar-se de que o usuário pertence ao grupo
    if ($grupoId) {
        $stmtGrupo = $pdo->prepare("SELECT id FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
        $stmtGrupo->execute([$grupoId, $userId]);
        if (!$stmtGrupo->fetch()) {
            setFlash('error', 'Você não pertence a este grupo.');
            header('Location: ../views/lists.php');
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO listas (titulo, descricao, tipo, icone, cor, usuario_id, grupo_id, status, criado_em)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'ativa', NOW())
    ");
    $stmt->execute([$titulo, $descricao, $tipo, $icone, $cor, $userId, $grupoId]);
    $novaListaId = $pdo->lastInsertId();

    setFlash('success', 'Lista "' . clean($titulo) . '" criada com sucesso!');
    header('Location: ../views/list_view.php?id=' . $novaListaId);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao criar a lista: ' . $e->getMessage());
    header('Location: ../views/lists.php');
    exit;
}
