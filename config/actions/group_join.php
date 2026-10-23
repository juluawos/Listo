<?php
/**
 * Listo - Entrar em um Grupo via Código de Convite
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

$codigo = strtoupper(trim($_POST['codigo_convite'] ?? ''));

if (empty($codigo)) {
    setFlash('warning', 'Digite o código de convite do grupo.');
    header('Location: ../views/groups.php');
    exit;
}

try {
    $pdo = getDB();

    $stmtGrupo = $pdo->prepare("SELECT id, nome FROM grupos WHERE UPPER(codigo_convite) = ?");
    $stmtGrupo->execute([$codigo]);
    $grupo = $stmtGrupo->fetch();

    if (!$grupo) {
        setFlash('error', 'Código de convite inválido ou expirado. Verifique com quem o compartilhou.');
        header('Location: ../views/groups.php');
        exit;
    }

    $grupoId = $grupo['id'];

    // Verificar se já é membro
    $stmtCheck = $pdo->prepare("SELECT id FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
    $stmtCheck->execute([$grupoId, $userId]);
    if ($stmtCheck->fetch()) {
        setFlash('info', 'Você já é membro do grupo "' . clean($grupo['nome']) . '".');
        header('Location: ../views/group_view.php?id=' . $grupoId);
        exit;
    }

    // Inserir como membro
    $stmtEntrar = $pdo->prepare("
        INSERT INTO membros_grupo (grupo_id, usuario_id, papel, entrou_em)
        VALUES (?, ?, 'membro', NOW())
    ");
    $stmtEntrar->execute([$grupoId, $userId]);

    setFlash('success', 'Parabéns! Você entrou no grupo "' . clean($grupo['nome']) . '".');
    header('Location: ../views/group_view.php?id=' . $grupoId);
    exit;

} catch (Exception $e) {
    setFlash('error', 'Erro ao ingressar no grupo: ' . $e->getMessage());
    header('Location: ../views/groups.php');
    exit;
}
