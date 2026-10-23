<?php
$pageTitle = 'Grupos & Família';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = $_SESSION['usuario_id'];
$pdo = getDB();

// Buscar todos os grupos dos quais o usuário é membro ou dono
$stmt = $pdo->prepare("
    SELECT g.*, 
           mg.papel, 
           mg.entrou_em,
           u.nome as dono_nome,
           (SELECT COUNT(*) FROM membros_grupo WHERE grupo_id = g.id) as total_membros,
           (SELECT COUNT(*) FROM listas WHERE grupo_id = g.id AND status = 'ativa') as total_listas
    FROM grupos g
    JOIN membros_grupo mg ON g.id = mg.grupo_id
    JOIN usuarios u ON g.criado_por = u.id
    WHERE mg.usuario_id = ?
    ORDER BY g.criado_em DESC
");
$stmt->execute([$userId]);
$grupos = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Topo -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i data-lucide="users" class="w-8 h-8 text-indigo-600"></i>
                <span>Grupos & Família</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Compartilhe listas e colabore em tempo real com familiares, amigos ou colegas.
            </p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" data-modal-open="modal-entrar-grupo" class="flex-1 sm:flex-initial bg-white text-slate-700 hover:bg-slate-50 font-bold py-3 px-4 rounded-2xl border border-slate-200 shadow-sm transition flex items-center justify-center gap-2 text-sm">
                <i data-lucide="key" class="w-4 h-4 text-purple-600"></i>
                <span>Digitar Código</span>
            </button>
            <button type="button" data-modal-open="modal-criar-grupo" class="flex-1 sm:flex-initial bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-5 rounded-2xl shadow-lg shadow-indigo-100 transition flex items-center justify-center gap-2 text-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Criar Grupo</span>
            </button>
        </div>
    </div>

    <!-- Lista de Grupos -->
    <?php if (empty($grupos)): ?>
        <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center">
            <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="users-2" class="w-8 h-8"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Você ainda não está em nenhum grupo</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-5">
                Crie um grupo para sua família ou insira um código de convite para compartilhar listas colaborativas.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <button type="button" data-modal-open="modal-criar-grupo" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Criar Meu Grupo
                </button>
                <button type="button" data-modal-open="modal-entrar-grupo" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-4 py-2.5 rounded-xl transition">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    Entrar com Código
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($grupos as $g): 
                $isAdmin = ($g['papel'] === 'admin');
            ?>
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <!-- Cabeçalho -->
                        <div class="flex items-center justify-between gap-2 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-500 to-indigo-600 text-white flex items-center justify-center font-extrabold text-base shadow-md shadow-purple-100">
                                <?= getInitials($g['nome']) ?>
                            </div>

                            <span class="px-2.5 py-1 rounded-xl text-xs font-bold <?= $isAdmin ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $isAdmin ? 'Administrador' : 'Membro' ?>
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition mb-1">
                            <?= clean($g['nome']) ?>
                        </h3>

                        <p class="text-xs text-slate-500 line-clamp-2 mb-4">
                            <?= !empty($g['descricao']) ? clean($g['descricao']) : 'Grupo para organização conjunta de listas.' ?>
                        </p>

                        <!-- Indicadores -->
                        <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                                <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                                <span><strong><?= $g['total_membros'] ?></strong> membros</span>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                                <i data-lucide="list" class="w-4 h-4 text-indigo-600"></i>
                                <span><strong><?= $g['total_listas'] ?></strong> listas</span>
                            </div>
                        </div>

                        <!-- Código de Convite com 1-Click Copy -->
                        <div class="bg-purple-50/60 border border-purple-100 rounded-2xl p-3 flex items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-purple-700 tracking-wider block">Código de Convite</span>
                                <code class="text-xs font-mono font-bold text-purple-900"><?= clean($g['codigo_convite']) ?></code>
                            </div>
                            <button type="button" data-copy-text="<?= clean($g['codigo_convite']) ?>" class="p-2 bg-white hover:bg-purple-100 text-purple-700 rounded-xl border border-purple-200 transition shadow-sm" title="Copiar código">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Rodapé com Link -->
                    <div class="mt-5 pt-4 border-t border-slate-100">
                        <a href="group_view.php?id=<?= $g['id'] ?>" class="w-full bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 font-bold text-xs py-2.5 px-3 rounded-xl transition flex items-center justify-center gap-1.5">
                            <span>Ver Grupo & Listas</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- MODAL: CRIAR GRUPO -->
<div id="modal-criar-grupo" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 animate-modal">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Criar Novo Grupo</h3>
            </div>
            <button type="button" data-modal-close="modal-criar-grupo" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/group_create.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="grupo_nome">Nome do Grupo *</label>
                <input type="text" id="grupo_nome" name="nome" required placeholder="Ex: Família Silva, Viagem Europa 2026..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="grupo_desc">Descrição (Opcional)</label>
                <textarea id="grupo_desc" name="descricao" rows="2" placeholder="Para que serve este grupo..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" data-modal-close="modal-criar-grupo" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Criar Grupo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ENTRAR EM GRUPO VIA CÓDIGO -->
<div id="modal-entrar-grupo" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 animate-modal">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center">
                    <i data-lucide="key" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Entrar em Grupo</h3>
            </div>
            <button type="button" data-modal-close="modal-entrar-grupo" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/group_join.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="codigo_convite">Código de Convite</label>
                <input type="text" id="codigo_convite" name="codigo_convite" required placeholder="LISTO-..." class="w-full uppercase font-mono tracking-wider px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" data-modal-close="modal-entrar-grupo" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Ingressar no Grupo
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
