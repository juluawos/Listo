<?php
/**
 * Listo - Controle de Autenticação e Autorização
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/functions.php';

/**
 * Verifica se o usuário atual está logado
 */
function isLoggedIn() {
    return !empty($_SESSION['usuario_id']);
}

/**
 * Exige login para acessar a página atual
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Faça login para acessar esta página.');
        header('Location: ' . getBasePath() . 'views/login.php');
        exit;
    }
}

/**
 * Retorna os dados do usuário conectado
 */
function currentUser() {
    if (!isLoggedIn()) return null;

    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id, nome, email, avatar_cor, criado_em FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $cachedUser = $user;
            return $user;
        }
    } catch (Exception $e) {
        // Fallback para os dados guardados na sessão
    }

    return [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
        'email' => $_SESSION['usuario_email'] ?? '',
        'avatar_cor' => $_SESSION['usuario_avatar_cor'] ?? 'indigo'
    ];
}

/**
 * Retorna o caminho base relativo para links e assets
 */
function getBasePath() {
    // Detecta se estamos dentro de /views/ ou /actions/ para ajustar os caminhos
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($script, '/views/') !== false || strpos($script, '/actions/') !== false) {
        return '../';
    }
    return '';
}

/**
 * Verifica permissões de acesso a uma lista
 *
 * @param PDO $pdo
 * @param int $listId
 * @param int $userId
 * @return array|false Retorna informações de permissão ou false se sem acesso
 */
function getListPermissions($pdo, $listId, $userId) {
    $stmt = $pdo->prepare("
        SELECT l.*, 
               u.nome as criador_nome,
               g.nome as grupo_nome, 
               g.criado_por as grupo_dono_id
        FROM listas l
        JOIN usuarios u ON l.usuario_id = u.id
        LEFT JOIN grupos g ON l.grupo_id = g.id
        WHERE l.id = ?
    ");
    $stmt->execute([$listId]);
    $list = $stmt->fetch();

    if (!$list) {
        return false;
    }

    $isOwner = ((int)$list['usuario_id'] === (int)$userId);
    $canView = false;
    $canEdit = false;
    $canDelete = false;
    $isAdmin = false;
    $userGroupRole = null;

    if (empty($list['grupo_id'])) {
        // Lista privada
        if ($isOwner) {
            $canView = true;
            $canEdit = true;
            $canDelete = true;
        }
    } else {
        // Lista vinculada a grupo
        $stmtMember = $pdo->prepare("SELECT papel FROM membros_grupo WHERE grupo_id = ? AND usuario_id = ?");
        $stmtMember->execute([$list['grupo_id'], $userId]);
        $membership = $stmtMember->fetch();

        if ($membership) {
            $userGroupRole = $membership['papel'];
            $canView = true;
            $isAdmin = ($membership['papel'] === 'admin' || (int)$list['grupo_dono_id'] === (int)$userId);

            // Regra do requisito: Apenas o criador da lista ou administrador do grupo pode excluir a lista.
            // Membros podem ver, criar itens e editar itens.
            $canEdit = true;
            $canDelete = ($isOwner || $isAdmin);
        }
    }

    if (!$canView) {
        return false;
    }

    return [
        'can_view' => $canView,
        'can_edit' => $canEdit,
        'can_delete' => $canDelete,
        'is_owner' => $isOwner,
        'is_admin' => $isAdmin,
        'group_role' => $userGroupRole,
        'list' => $list
    ];
}

/**
 * Verifica permissões em um grupo
 */
function getGroupPermissions($pdo, $groupId, $userId) {
    $stmt = $pdo->prepare("
        SELECT g.*, mg.papel, mg.entrou_em
        FROM grupos g
        JOIN membros_grupo mg ON g.id = mg.grupo_id
        WHERE g.id = ? AND mg.usuario_id = ?
    ");
    $stmt->execute([$groupId, $userId]);
    $group = $stmt->fetch();

    if (!$group) {
        return false;
    }

    $isAdmin = ($group['papel'] === 'admin' || (int)$group['criado_por'] === (int)$userId);

    return [
        'is_member' => true,
        'is_admin' => $isAdmin,
        'role' => $group['papel'],
        'group' => $group
    ];
}
