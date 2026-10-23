<?php
$pageTitle = 'Meu Perfil';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = $_SESSION['usuario_id'];
$pdo = getDB();

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$usuario = $stmt->fetch();

// Estatísticas para o perfil
$stmtMinhasListas = $pdo->prepare("SELECT COUNT(*) FROM listas WHERE usuario_id = ? AND status = 'ativa'");
$stmtMinhasListas->execute([$userId]);
$minhasListasCount = $stmtMinhasListas->fetchColumn();

$stmtMeusGrupos = $pdo->prepare("SELECT COUNT(*) FROM membros_grupo WHERE usuario_id = ?");
$stmtMeusGrupos->execute([$userId]);
$meusGruposCount = $stmtMeusGrupos->fetchColumn();
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Topo -->
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
            <i data-lucide="user" class="w-8 h-8 text-indigo-600"></i>
            <span>Perfil & Configurações da Conta</span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">Gerencie suas informações pessoais e credenciais de acesso.</p>
    </div>

    <!-- Card de Perfil e Estatísticas -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <div class="w-20 h-20 rounded-3xl bg-indigo-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                <?= getInitials($usuario['nome']) ?>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-slate-900"><?= clean($usuario['nome']) ?></h2>
                <p class="text-sm text-slate-500"><?= clean($usuario['email']) ?></p>
                <span class="inline-block mt-2 text-xs text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                    Membro desde <?= formatDate($usuario['criado_em']) ?>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="flex-1 sm:flex-initial text-center bg-slate-50 border border-slate-100 px-4 py-3 rounded-2xl">
                <span class="text-xs text-slate-400 block">Listas Criadas</span>
                <strong class="text-xl font-bold text-slate-800"><?= $minhasListasCount ?></strong>
            </div>
            <div class="flex-1 sm:flex-initial text-center bg-slate-50 border border-slate-100 px-4 py-3 rounded-2xl">
                <span class="text-xs text-slate-400 block">Grupos</span>
                <strong class="text-xl font-bold text-purple-600"><?= $meusGruposCount ?></strong>
            </div>
        </div>
    </div>

    <!-- Formulário de Alteração de Dados -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-5 flex items-center gap-2">
            <i data-lucide="settings-2" class="w-5 h-5 text-indigo-600"></i>
            <span>Editar Informações Pessoais</span>
        </h3>

        <form action="../actions/profile_update.php" method="POST" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" value="<?= clean($usuario['nome']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="email">E-mail Cadastrado</label>
                    <input type="email" id="email" name="email" value="<?= clean($usuario['email']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <!-- Alterar Senha (Opcional) -->
            <div class="pt-4 border-t border-slate-100 space-y-4">
                <h4 class="text-sm font-bold text-slate-800">Alterar Senha de Acesso (Opcional)</h4>
                <p class="text-xs text-slate-500">Deixe em branco se não desejar alterar sua senha atual.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo de 6 caracteres" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="senha_atual">Senha Atual (para confirmar alteração)</label>
                        <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha atual" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-indigo-100 transition flex items-center gap-2 text-sm">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Salvar Alterações</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Sair da Conta -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
        <div>
            <h4 class="text-sm font-bold text-slate-900">Encerrar Sessão</h4>
            <p class="text-xs text-slate-500">Desconectar sua conta neste dispositivo.</p>
        </div>
        <form action="../actions/auth_logout.php" method="POST">
            <button type="submit" data-confirm="Deseja realmente sair da sua conta?" data-confirm-title="Encerrar Sessão" data-confirm-color="amber" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs px-4 py-2.5 rounded-xl border border-rose-200 transition flex items-center gap-1.5">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Sair Agora</span>
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
