<?php
$pageTitle = 'Minhas Listas';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = $_SESSION['usuario_id'];
$pdo = getDB();

// Filtros
$tipoFiltro = trim($_GET['tipo'] ?? '');
$visibilidade = trim($_GET['visibilidade'] ?? '');
$busca = trim($_GET['busca'] ?? '');

// Montar query dinamicamente
$sql = "
    SELECT DISTINCT l.*, 
           u.nome as criador_nome,
           g.nome as grupo_nome,
           (SELECT COUNT(*) FROM itens_lista WHERE lista_id = l.id AND status = 'ativo') as total_itens,
           (SELECT COUNT(*) FROM itens_lista WHERE lista_id = l.id AND concluido = 1 AND status = 'ativo') as itens_concluidos
    FROM listas l
    JOIN usuarios u ON l.usuario_id = u.id
    LEFT JOIN membros_grupo mg ON l.grupo_id = mg.grupo_id
    LEFT JOIN grupos g ON l.grupo_id = g.id
    WHERE l.status = 'ativa' 
      AND (l.usuario_id = :user_id1 OR mg.usuario_id = :user_id2)
";

$params = [
    ':user_id1' => $userId,
    ':user_id2' => $userId
];

if (!empty($tipoFiltro)) {
    $sql .= " AND l.tipo = :tipo";
    $params[':tipo'] = $tipoFiltro;
}

if ($visibilidade === 'privada') {
    $sql .= " AND l.grupo_id IS NULL AND l.usuario_id = :my_user";
    $params[':my_user'] = $userId;
} elseif ($visibilidade === 'grupo') {
    $sql .= " AND l.grupo_id IS NOT NULL";
}

if (!empty($busca)) {
    $sql .= " AND (l.titulo LIKE :busca OR l.descricao LIKE :busca)";
    $params[':busca'] = '%' . $busca . '%';
}

$sql .= " ORDER BY l.atualizado_em DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listas = $stmt->fetchAll();

// Grupos para o modal
$stmtMeusGrupos = $pdo->prepare("
    SELECT g.id, g.nome 
    FROM grupos g
    JOIN membros_grupo mg ON g.id = mg.grupo_id
    WHERE mg.usuario_id = ?
    ORDER BY g.nome ASC
");
$stmtMeusGrupos->execute([$userId]);
$meusGrupos = $stmtMeusGrupos->fetchAll();

$categories = getCategoryMeta();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Topo da Página -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i data-lucide="list-todo" class="w-8 h-8 text-indigo-600"></i>
                <span>Gerenciador de Listas</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Crie, organize e colabore em tempo real com outras pessoas.</p>
        </div>

        <button type="button" data-modal-open="modal-nova-lista" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-5 rounded-2xl shadow-lg shadow-indigo-100 transition flex items-center justify-center gap-2">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>Criar Nova Lista</span>
        </button>
    </div>

    <!-- Barra de Filtros e Busca -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
        <!-- Categorias Tabs em Carrossel Horizontal -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            <a href="lists.php" class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition <?= empty($tipoFiltro) ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Todas as Categorias
            </a>
            <?php foreach ($categories as $catKey => $cat): ?>
                <a href="lists.php?tipo=<?= $catKey ?><?= !empty($visibilidade) ? '&visibilidade=' . $visibilidade : '' ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-1.5 <?= $tipoFiltro === $catKey ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <i data-lucide="<?= $cat['icone'] ?>" class="w-3.5 h-3.5"></i>
                    <span><?= $cat['nome'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Linha de Busca e Visibilidade -->
        <form method="GET" action="lists.php" class="flex flex-col sm:flex-row items-center gap-3 pt-1">
            <?php if (!empty($tipoFiltro)): ?>
                <input type="hidden" name="tipo" value="<?= clean($tipoFiltro) ?>">
            <?php endif; ?>

            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="busca" value="<?= clean($busca) ?>" placeholder="Buscar listas por título ou descrição..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="visibilidade" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="" <?= empty($visibilidade) ? 'selected' : '' ?>>Todas as Listas</option>
                    <option value="privada" <?= $visibilidade === 'privada' ? 'selected' : '' ?>>Apenas Privadas</option>
                    <option value="grupo" <?= $visibilidade === 'grupo' ? 'selected' : '' ?>>Apenas de Grupos</option>
                </select>

                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2 rounded-xl transition">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Grid de Listas -->
    <?php if (empty($listas)): ?>
        <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-12 text-center">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="clipboard-list" class="w-8 h-8"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Nenhuma lista encontrada</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-5">
                <?= !empty($busca) || !empty($tipoFiltro) ? 'Tente alterar os filtros de busca ou categoria.' : 'Crie sua primeira lista para organizar compras, receitas ou afazeres!' ?>
            </p>
            <button type="button" data-modal-open="modal-nova-lista" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Criar Nova Lista
            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($listas as $lista): 
                $catMeta = getCategoryMeta($lista['tipo']);
                $pct = $lista['total_itens'] > 0 ? round(($lista['itens_concluidos'] / $lista['total_itens']) * 100) : 0;
                $isOwner = ((int)$lista['usuario_id'] === (int)$userId);
            ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex flex-col justify-between overflow-hidden group">
                    <div class="p-5">
                        
                        <!-- Cabeçalho do Card -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold <?= $catMeta['bg_light'] ?> <?= $catMeta['text_color'] ?>">
                                <i data-lucide="<?= $catMeta['icone'] ?>" class="w-3.5 h-3.5"></i>
                                <span><?= $catMeta['nome'] ?></span>
                            </span>

                            <?php if (!empty($lista['grupo_nome'])): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-200" title="Grupo: <?= clean($lista['grupo_nome']) ?>">
                                    <i data-lucide="users" class="w-3 h-3"></i>
                                    <span class="truncate max-w-[120px]"><?= clean($lista['grupo_nome']) ?></span>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                    <i data-lucide="lock" class="w-3 h-3"></i> Privada
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Título e Descrição -->
                        <a href="list_view.php?id=<?= $lista['id'] ?>" class="block">
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition mb-1">
                                <?= clean($lista['titulo']) ?>
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2 min-h-[32px]">
                                <?= !empty($lista['descricao']) ? clean($lista['descricao']) : 'Sem descrição.' ?>
                            </p>
                        </a>

                        <!-- Informações do Criador -->
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-2">
                            <span>Criada por <?= $isOwner ? 'você' : clean($lista['criador_nome']) ?></span>
                            <span>&bull;</span>
                            <span><?= formatDate($lista['atualizado_em']) ?></span>
                        </div>
                    </div>

                    <!-- Rodapé do Card com Progresso e Botões de Ação -->
                    <div class="bg-slate-50/70 p-4 border-t border-slate-100 space-y-3">
                        <div>
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                <span><?= $lista['itens_concluidos'] ?> / <?= $lista['total_itens'] ?> concluídos</span>
                                <span class="font-bold text-slate-700"><?= $pct ?>%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-indigo-600 h-1.5 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-1">
                            <a href="list_view.php?id=<?= $lista['id'] ?>" class="flex-1 text-center bg-white hover:bg-slate-100 text-indigo-700 font-bold text-xs py-2 px-3 rounded-xl border border-slate-200 transition flex items-center justify-center gap-1.5 shadow-sm">
                                <span>Abrir Lista</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>

                            <form action="../actions/list_delete.php" method="POST" class="inline">
                                <input type="hidden" name="lista_id" value="<?= $lista['id'] ?>">
                                <input type="hidden" name="acao" value="lixeira">
                                <button type="submit" data-confirm="Deseja mover a lista '<?= clean($lista['titulo']) ?>' para a lixeira?" data-confirm-title="Mover para a Lixeira" data-confirm-color="amber" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Mover para a lixeira">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- MODAL: NOVA LISTA -->
<div id="modal-nova-lista" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 sm:p-7 animate-modal max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="folder-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Criar Nova Lista</h3>
                    <p class="text-xs text-slate-500">Defina o tipo e compartilhe com seu grupo</p>
                </div>
            </div>
            <button type="button" data-modal-close="modal-nova-lista" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/list_create.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="modal_titulo">Título da Lista *</label>
                <input type="text" id="modal_titulo" name="titulo" required placeholder="Ex: Supermercado do Mês, Livros para Ler..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="modal_tipo">Categoria / Tipo de Lista *</label>
                <select id="modal_tipo" name="tipo" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="supermercado" <?= $tipoFiltro === 'supermercado' ? 'selected' : '' ?>>🛒 Supermercado (com quant., unidade e preços)</option>
                    <option value="filmes" <?= $tipoFiltro === 'filmes' ? 'selected' : '' ?>>🎬 Filmes & Séries (streaming, gênero, notas)</option>
                    <option value="receitas" <?= $tipoFiltro === 'receitas' ? 'selected' : '' ?>>🍳 Receitas (ingredientes, rendimento, preparo)</option>
                    <option value="tarefas" <?= empty($tipoFiltro) || $tipoFiltro === 'tarefas' ? 'selected' : '' ?>>✅ Tarefas & Rotina (prazos, prioridades)</option>
                    <option value="viagens" <?= $tipoFiltro === 'viagens' ? 'selected' : '' ?>>✈️ Viagens & Bagagem (checklist completo)</option>
                    <option value="outros" <?= $tipoFiltro === 'outros' ? 'selected' : '' ?>>📝 Outros (personalizada)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="modal_grupo_id">Compartilhar com Grupo / Família</label>
                <select id="modal_grupo_id" name="grupo_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">🔒 Lista Privada (Apenas Você)</option>
                    <?php foreach ($meusGrupos as $g): ?>
                        <option value="<?= $g['id'] ?>">👥 <?= clean($g['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="modal_descricao">Descrição (Opcional)</label>
                <textarea id="modal_descricao" name="descricao" rows="2" placeholder="Detalhes adicionais..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" data-modal-close="modal-nova-lista" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Criar Lista
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
