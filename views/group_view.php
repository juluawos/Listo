<?php
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];
$pdo = getDB();

$groupId = (int)($_GET['id'] ?? 0);
if (!$groupId) {
    setFlash('error', 'Grupo não especificado.');
    header('Location: groups.php');
    exit;
}

$perm = getGroupPermissions($pdo, $groupId, $userId);
if (!$perm) {
    setFlash('error', 'Você não pertence a este grupo.');
    header('Location: groups.php');
    exit;
}

$grupo = $perm['group'];
$isAdmin = $perm['is_admin'];
$pageTitle = 'Grupo: ' . $grupo['nome'];

// Buscar membros do grupo
$stmtMembros = $pdo->prepare("
    SELECT u.id, u.nome, u.email, u.avatar_cor, mg.papel, mg.entrou_em
    FROM membros_grupo mg
    JOIN usuarios u ON mg.usuario_id = u.id
    WHERE mg.grupo_id = ?
    ORDER BY mg.papel ASC, mg.entrou_em ASC
");
$stmtMembros->execute([$groupId]);
$membros = $stmtMembros->fetchAll();

// Buscar listas vinculadas ao grupo
$stmtListas = $pdo->prepare("
    SELECT l.*, 
           u.nome as criador_nome,
           (SELECT COUNT(*) FROM itens_lista WHERE lista_id = l.id AND status = 'ativo') as total_itens,
           (SELECT COUNT(*) FROM itens_lista WHERE lista_id = l.id AND concluido = 1 AND status = 'ativo') as itens_concluidos
    FROM listas l
    JOIN usuarios u ON l.usuario_id = u.id
    WHERE l.grupo_id = ? AND l.status = 'ativa'
    ORDER BY l.atualizado_em DESC
");
$stmtListas->execute([$groupId]);
$listas = $stmtListas->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- Navegação de Retorno -->
    <div class="flex items-center justify-between">
        <a href="groups.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Voltar para Grupos</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Botão Sair do Grupo (Se não for criador principal) -->
            <?php if ((int)$grupo['criado_por'] !== (int)$userId): ?>
                <form action="../actions/group_member_remove.php" method="POST" class="inline">
                    <input type="hidden" name="grupo_id" value="<?= $grupo['id'] ?>">
                    <input type="hidden" name="usuario_id" value="<?= $userId ?>">
                    <button type="submit" data-confirm="Deseja realmente sair deste grupo?" data-confirm-title="Sair do Grupo" data-confirm-color="amber" class="text-xs font-bold text-slate-500 hover:text-rose-600 px-3 py-2 rounded-xl hover:bg-rose-50 transition flex items-center gap-1.5">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Sair do Grupo</span>
                    </button>
                </form>
            <?php endif; ?>

            <!-- Botão Excluir Grupo (Apenas Dono/Admin) -->
            <?php if ($isAdmin): ?>
                <form action="../actions/group_delete.php" method="POST" class="inline">
                    <input type="hidden" name="grupo_id" value="<?= $grupo['id'] ?>">
                    <button type="submit" data-confirm="ATENÇÃO: Deseja realmente excluir este grupo? As listas compartilhadas permanecerão com seus respectivos criadores." data-confirm-title="Excluir Grupo" data-confirm-color="rose" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Excluir Grupo">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cabeçalho do Grupo e Código de Convite -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-extrabold text-xl shadow-lg shadow-purple-100 shrink-0">
                <?= getInitials($grupo['nome']) ?>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                        Grupo Compartilhado
                    </span>
                    <span class="text-xs text-slate-400">
                        Criado em <?= formatDate($grupo['criado_em']) ?>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    <?= clean($grupo['nome']) ?>
                </h1>
                <?php if (!empty($grupo['descricao'])): ?>
                    <p class="text-sm text-slate-600 mt-1 max-w-xl">
                        <?= clean($grupo['descricao']) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Banner com Código de Convite -->
        <div class="w-full md:w-auto bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Código de Convite</span>
                <code class="text-base font-mono font-extrabold text-indigo-700"><?= clean($grupo['codigo_convite']) ?></code>
            </div>
            <button type="button" data-copy-text="<?= clean($grupo['codigo_convite']) ?>" class="bg-white hover:bg-indigo-50 text-indigo-700 font-bold text-xs px-3.5 py-2.5 rounded-xl border border-indigo-200 shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="copy" class="w-4 h-4"></i>
                <span>Copiar</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- COLUNA DA ESQUERDA: MEMBROS E CONVITE (1 Coluna) -->
        <div class="space-y-6">
            
            <!-- Convidar Membro por E-mail -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-indigo-600"></i>
                    <span>Convidar por E-mail</span>
                </h3>
                <p class="text-xs text-slate-500 mb-3">
                    Adicione um usuário já cadastrado digitando o e-mail dele:
                </p>

                <form action="../actions/group_invite.php" method="POST" class="space-y-2.5">
                    <input type="hidden" name="grupo_id" value="<?= $grupo['id'] ?>">
                    <input type="email" name="email" required placeholder="email.cadastrado@email.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow transition flex items-center justify-center gap-1.5">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span>Adicionar Membro</span>
                    </button>
                </form>
            </div>

            <!-- Lista de Membros Atuais -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                        <span>Membros (<?= count($membros) ?>)</span>
                    </h3>
                </div>

                <div class="divide-y divide-slate-100">
                    <?php foreach ($membros as $m): 
                        $isMemAdmin = ($m['papel'] === 'admin');
                        $isCurrent = ((int)$m['id'] === (int)$userId);
                    ?>
                        <div class="py-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                    <?= getInitials($m['nome']) ?>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-slate-800 truncate"><?= clean($m['nome']) ?></span>
                                        <?php if ($isCurrent): ?>
                                            <span class="text-[10px] text-slate-400 font-medium">(você)</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-[11px] text-slate-400 truncate block"><?= clean($m['email']) ?></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= $isMemAdmin ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= $isMemAdmin ? 'Admin' : 'Membro' ?>
                                </span>

                                <!-- Remover Membro (Apenas Admin pode remover outros membros) -->
                                <?php if ($isAdmin && !$isCurrent && (int)$grupo['criado_por'] !== (int)$m['id']): ?>
                                    <form action="../actions/group_member_remove.php" method="POST" class="inline">
                                        <input type="hidden" name="grupo_id" value="<?= $grupo['id'] ?>">
                                        <input type="hidden" name="usuario_id" value="<?= $m['id'] ?>">
                                        <button type="submit" data-confirm="Remover '<?= clean($m['nome']) ?>' deste grupo?" data-confirm-title="Remover Membro" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Remover membro">
                                            <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- COLUNA DA DIREITA: LISTAS DO GRUPO (2 Colunas) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="list-todo" class="w-5 h-5 text-indigo-600"></i>
                        <span>Listas do Grupo</span>
                    </h2>
                    <p class="text-xs text-slate-500">Todos os membros deste grupo podem visualizar e atualizar os itens.</p>
                </div>

                <button type="button" data-modal-open="modal-nova-lista-grupo" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Nova Lista para o Grupo</span>
                </button>
            </div>

            <?php if (empty($listas)): ?>
                <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-10 text-center">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="folder-plus" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800 mb-1">Nenhuma lista compartilhada ainda</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                        Crie a primeira lista colaborativa deste grupo (compras da semana, maratona de séries, etc.).
                    </p>
                    <button type="button" data-modal-open="modal-nova-lista-grupo" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Criar Lista Agora
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($listas as $l): 
                        $catMeta = getCategoryMeta($l['tipo']);
                        $pct = $l['total_itens'] > 0 ? round(($l['itens_concluidos'] / $l['total_itens']) * 100) : 0;
                    ?>
                        <a href="list_view.php?id=<?= $l['id'] ?>" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex flex-col justify-between group">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold <?= $catMeta['bg_light'] ?> <?= $catMeta['text_color'] ?>">
                                        <i data-lucide="<?= $catMeta['icone'] ?>" class="w-3.5 h-3.5"></i>
                                        <span><?= $catMeta['nome'] ?></span>
                                    </span>
                                </div>

                                <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition mb-1">
                                    <?= clean($l['titulo']) ?>
                                </h3>

                                <div class="text-[11px] text-slate-400 mb-3">
                                    Por <?= clean($l['criador_nome']) ?>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100">
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                    <span><?= $l['itens_concluidos'] ?> / <?= $l['total_itens'] ?> concluídos</span>
                                    <span class="font-bold text-slate-700"><?= $pct ?>%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- MODAL: NOVA LISTA VINCULADA AO GRUPO -->
<div id="modal-nova-lista-grupo" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 sm:p-7 animate-modal max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900">Nova Lista para <?= clean($grupo['nome']) ?></h3>
            <button type="button" data-modal-close="modal-nova-lista-grupo" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/list_create.php" method="POST" class="space-y-4">
            <input type="hidden" name="grupo_id" value="<?= $grupo['id'] ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Título da Lista *</label>
                <input type="text" name="titulo" required placeholder="Ex: Compras da Semana, Playlist..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Categoria / Tipo *</label>
                <select name="tipo" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="supermercado">🛒 Supermercado (com quant., unidade e preços)</option>
                    <option value="filmes">🎬 Filmes & Séries (streaming, gênero, notas)</option>
                    <option value="receitas">🍳 Receitas (ingredientes, rendimento, preparo)</option>
                    <option value="tarefas" selected>✅ Tarefas & Rotina (prazos, prioridades)</option>
                    <option value="viagens">✈️ Viagens & Bagagem (checklist completo)</option>
                    <option value="outros">📝 Outros (personalizada)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Descrição (Opcional)</label>
                <textarea name="descricao" rows="2" placeholder="Detalhes da lista..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" data-modal-close="modal-nova-lista-grupo" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Criar Lista no Grupo
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
