<?php
/**
 * Listo - Ação de Cadastro de Novo Usuário
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/register.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$senha = $_POST['senha'] ?? '';
$senhaConfirmar = $_POST['senha_confirmar'] ?? '';

// Validações
if (empty($nome) || empty($email) || empty($senha)) {
    setFlash('error', 'Por favor, preencha todos os campos obrigatórios.');
    header('Location: ../views/register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'O formato do e-mail informado é inválido.');
    header('Location: ../views/register.php');
    exit;
}

if (strlen($senha) < 6) {
    setFlash('error', 'A senha deve conter no mínimo 6 caracteres.');
    header('Location: ../views/register.php');
    exit;
}

if ($senha !== $senhaConfirmar) {
    setFlash('error', 'A confirmação de senha não confere.');
    header('Location: ../views/register.php');
    exit;
}

try {
    $pdo = getDB();

    // Verificar se o e-mail já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        setFlash('error', 'Este e-mail já está cadastrado. Tente fazer login.');
        header('Location: ../views/login.php');
        exit;
    }

    // Hash seguro da senha com password_hash
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Cores aleatórias para o avatar
    $cores = ['indigo', 'emerald', 'rose', 'amber', 'sky', 'violet'];
    $avatarCor = $cores[array_rand($cores)];

    // Inserir usuário
    $stmtInsert = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha, avatar_cor, criado_em) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmtInsert->execute([$nome, $email, $senhaHash, $avatarCor]);
    $novoUsuarioId = $pdo->lastInsertId();

    // Criar uma lista inicial de boas-vindas para o usuário
    $stmtList = $pdo->prepare("
        INSERT INTO listas (titulo, descricao, tipo, icone, cor, usuario_id, status)
        VALUES ('Primeiros Passos no Listo', 'Dicas rápidas para aproveitar ao máximo a sua nova conta', 'tarefas', 'check-square', 'indigo', ?, 'ativa')
    ");
    $stmtList->execute([$novoUsuarioId]);
    $listaId = $pdo->lastInsertId();

    // Itens da lista inicial
    $stmtItem = $pdo->prepare("
        INSERT INTO itens_lista (lista_id, titulo, concluido, ordem, prioridade, criado_por)
        VALUES 
        (?, 'Explorar a categoria de Supermercado e ver cálculo de preços', 0, 1, 'alta', ?),
        (?, 'Criar um Grupo para compartilhar listas com família ou amigos', 0, 2, 'alta', ?),
        (?, 'Experimentar a lista de Filmes e Séries ou Receitas', 0, 3, 'media', ?)
    ");
    $stmtItem->execute([$listaId, $novoUsuarioId, $listaId, $novoUsuarioId, $listaId, $novoUsuarioId]);

    // Iniciar sessão
    $_SESSION['usuario_id'] = $novoUsuarioId;
    $_SESSION['usuario_nome'] = $nome;
    $_SESSION['usuario_email'] = $email;
    $_SESSION['usuario_avatar_cor'] = $avatarCor;

    setFlash('success', 'Conta criada com sucesso! Seja bem-vindo ao Listo.');
    header('Location: ../views/dashboard.php');
    exit;

} catch (Exception $e) {
    setFlash('error', 'Ocorreu um erro ao processar o cadastro: ' . $e->getMessage());
    header('Location: ../views/register.php');
    exit;
}
