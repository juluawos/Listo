<?php
/**
 * Listo - Criação de Novo Grupo (Família / Equipe)
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/groups.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (empty($nome)) {
    setFlash('warning', 'O nome do grupo é obrigatório.');
    header('Location: ../views/groups.php');
    exit;
}

try {
    $pdo = getDB();

    // Gerar código de convite único amigável (ex: LISTO-7K2X9P)
    $codigoConvite = 'LISTO-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

    // Criar o grupo
    $stmt = $pdo->prepare("
        INSERT INTO grupos (nome, descricao, codigo_convite, criado_por, criado_em)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$nome, $descricao, $codigoConvite, $userId]);
    $grupoId = $pdo->lastInsertId();

    // Adicionar criador como 'admin'
    $stmtMembro = $pdo->prepare("
        INSERT INTO membros_grupo (grupo_id, usuario_id, papel, entrou_em)
        VALUES (?, ?, 'admin', NOW())
    ");
    $stmtMembro->execute([$grupoId, $userId]);

    setFlash('success', 'Grupo "' . clean($nome) . '" criado com sucesso! Convide membros usando o código ou e-mail.');
    header('Location: ../views/group_view.php?id=' . $grupoId);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao criar grupo: ' . $e->getMessage());
    header('Location: ../views/groups.php');
    exit;
}
