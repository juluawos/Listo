<?php
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$userId = $_SESSION['usuario_id'];
$pdo = getDB();

$listaId = (int)($_GET['id'] ?? 0);
if (!$listaId) {
    setFlash('error', 'Lista não especificada.');
    header('Location: lists.php');
    exit;
}

$perm = getListPermissions($pdo, $listaId, $userId);
if (!$perm || !$perm['can_view']) {
    setFlash('error', 'Você não tem permissão para acessar esta lista ou ela não existe.');
    header('Location: lists.php');
    exit;
}

$lista = $perm['list'];
$catMeta = getCategoryMeta($lista['tipo']);
$pageTitle = $lista['titulo'];

// Buscar itens ativos da lista
$stmtItens = $pdo->prepare("
    SELECT il.*, u.nome as autor_nome 
    FROM itens_lista il
    JOIN usuarios u ON il.criado_por = u.id
    WHERE il.lista_id = ? AND il.status = 'ativo'
    ORDER BY il.concluido ASC, il.ordem ASC, il.id DESC
");
$stmtItens->execute([$listaId]);
$itens = $stmtItens->fetchAll();

// Métricas e totais
$totalItens = count($itens);
$itensConcluidos = 0;
$totalEstimado = 0.0;
$totalReal = 0.0;

foreach ($itens as $it) {
    if ($it['concluido']) {
        $itensConcluidos++;
        $totalReal += ((float)$it['quantidade'] * (float)$it['preco_real']);
    }
    $totalEstimado += ((float)$it['quantidade'] * (float)$it['preco_estimado']);
}

$pct = $totalItens > 0 ? round(($itensConcluidos / $totalItens) * 100) : 0;

// Grupos para modal de edição se for criador
$stmtGrupos = $pdo->prepare("
    SELECT g.id, g.nome 
    FROM grupos g
    JOIN membros_grupo mg ON g.id = mg.grupo_id
    WHERE mg.usuario_id = ?
");
$stmtGrupos->execute([$userId]);
$meusGrupos = $stmtGrupos->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Topo / Navegação de Retorno -->
    <div class="flex items-center justify-between">
        <a href="lists.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-indigo-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Voltar para Listas</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Botão Reativar Lista (Desmarcar todos os itens) -->
            <?php if ($itensConcluidos > 0): ?>
                <form action="../actions/list_reactivate.php" method="POST" class="inline">
                    <input type="hidden" name="lista_id" value="<?= $lista['id'] ?>">
                    <button type="submit" data-confirm="Deseja desmarcar todos os itens para reutilizar a lista?" data-confirm-title="Reativar Lista" data-confirm-color="indigo" class="inline-flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-2 rounded-xl transition shadow-sm" title="Desmarca todos os itens para nova compra ou rotina">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Reativar Lista</span>
                    </button>
                </form>
            <?php endif; ?>

            <!-- Botão Editar Lista -->
            <?php if ($perm['can_edit']): ?>
                <button type="button" data-modal-open="modal-editar-lista" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-white rounded-xl border border-slate-200 transition" title="Editar Configurações da Lista">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                </button>
            <?php endif; ?>

            <!-- Botão Mover para Lixeira -->
            <?php if ($perm['can_delete']): ?>
                <form action="../actions/list_delete.php" method="POST" class="inline">
                    <input type="hidden" name="lista_id" value="<?= $lista['id'] ?>">
                    <input type="hidden" name="acao" value="lixeira">
                    <button type="submit" data-confirm="Deseja mover a lista inteira para a lixeira?" data-confirm-title="Mover para a Lixeira" data-confirm-color="amber" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl border border-slate-200 transition" title="Excluir Lista">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cabeçalho Informativo da Lista -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm relative overflow-hidden">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl <?= $catMeta['bg_light'] ?> <?= $catMeta['text_color'] ?> flex items-center justify-center shrink-0 shadow-sm border <?= $catMeta['border_color'] ?>">
                    <i data-lucide="<?= $catMeta['icone'] ?>" class="w-7 h-7"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold <?= $catMeta['bg_light'] ?> <?= $catMeta['text_color'] ?>">
                            <?= $catMeta['nome'] ?>
                        </span>

                        <?php if (!empty($lista['grupo_nome'])): ?>
                            <a href="group_view.php?id=<?= $lista['grupo_id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition">
                                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                <span>Grupo: <?= clean($lista['grupo_nome']) ?></span>
                            </a>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium text-slate-500 bg-slate-100">
                                <i data-lucide="lock" class="w-3 h-3"></i> Privada
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        <?= clean($lista['titulo']) ?>
                    </h1>
                    
                    <?php if (!empty($lista['descricao'])): ?>
                        <p class="text-sm text-slate-600 mt-1 max-w-2xl">
                            <?= clean($lista['descricao']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="flex items-center gap-2 text-xs text-slate-400 mt-2">
                        <span>Criado por <strong><?= clean($lista['criador_nome']) ?></strong></span>
                        <span>&bull;</span>
                        <span>Atualizado em <?= formatDate($lista['atualizado_em'], true) ?></span>
                    </div>
                </div>
            </div>

            <!-- Botão Adicionar Item Principal -->
            <button type="button" data-modal-open="modal-adicionar-item" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-5 rounded-2xl shadow-lg shadow-indigo-100 transition flex items-center justify-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                <span>Adicionar <?= $lista['tipo'] === 'receitas' ? 'Receita' : ($lista['tipo'] === 'filmes' ? 'Filme/Série' : 'Item') ?></span>
            </button>
        </div>

        <!-- Barra de Progresso e Métricas Gerais -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5 font-medium">
                <span id="counter-summary"><span id="counter-done"><?= $itensConcluidos ?></span> de <span id="counter-total"><?= $totalItens ?></span> itens concluídos</span>
                <span id="counter-percentage" class="font-bold text-indigo-700"><?= $pct ?>%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: <?= $pct ?>%"></div>
            </div>
        </div>

        <!-- Painel de Totais Específico para SUPERMERCADO -->
        <?php if ($lista['tipo'] === 'supermercado'): ?>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-slate-100 text-xs">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <span class="text-slate-400 block mb-0.5">Total de Itens</span>
                    <strong class="text-sm text-slate-800"><?= $totalItens ?> produtos</strong>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <span class="text-slate-400 block mb-0.5">No Carrinho</span>
                    <strong class="text-sm text-emerald-600"><?= $itensConcluidos ?> comprados</strong>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                    <span class="text-slate-400 block mb-0.5">Gasto Estimado</span>
                    <strong class="text-sm text-slate-700"><?= formatCurrency($totalEstimado) ?></strong>
                </div>
                <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-200">
                    <span class="text-emerald-700 block mb-0.5 font-bold">Total Já Comprado</span>
                    <strong class="text-sm text-emerald-800"><?= formatCurrency($totalReal > 0 ? $totalReal : $totalEstimado) ?></strong>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- SEÇÃO DE ITENS / CONTEÚDO DA LISTA -->
    <div class="space-y-3">
        
        <?php if (empty($itens)): ?>
            <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-10 text-center">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="plus-circle" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Nenhum item adicionado ainda</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                    Adicione os itens necessários para começar a gerenciar sua lista com facilidade.
                </p>
                <button type="button" data-modal-open="modal-adicionar-item" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Adicionar Primeiro Item
                </button>
            </div>
        <?php else: ?>

            <!-- ============================================== -->
            <!-- RENDERIZAÇÃO: CATEGORIA SUPERMERCADO -->
            <!-- ============================================== -->
            <?php if ($lista['tipo'] === 'supermercado'): ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm divide-y divide-slate-100 overflow-hidden">
                    <?php foreach ($itens as $it): 
                        $precoItemEst = (float)$it['quantidade'] * (float)$it['preco_estimado'];
                        $precoItemReal = (float)$it['quantidade'] * (float)$it['preco_real'];
                    ?>
                        <div data-item-card class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition <?= $it['concluido'] ? 'bg-slate-50/60' : '' ?>">
                            <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="checkbox" 
                                           class="item-checkbox w-5 h-5 text-indigo-600 rounded-lg border-slate-300 focus:ring-indigo-500 cursor-pointer"
                                           <?= $it['concluido'] ? 'checked' : '' ?>
                                           onchange="toggleItemStatus(this, <?= $it['id'] ?>, '../actions/item_toggle.php')">
                                </label>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="item-title font-semibold text-sm <?= $it['concluido'] ? 'line-through text-slate-400' : 'text-slate-800' ?>">
                                            <?= clean($it['titulo']) ?>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600">
                                            <?= (float)$it['quantidade'] ?> <?= clean($it['unidade']) ?>
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-3 text-xs text-slate-400 mt-0.5">
                                        <?php if ((float)$it['preco_estimado'] > 0): ?>
                                            <span>Est: <?= formatCurrency($precoItemEst) ?></span>
                                        <?php endif; ?>
                                        <?php if ((float)$it['preco_real'] > 0): ?>
                                            <span class="text-emerald-600 font-semibold">Real: <?= formatCurrency($precoItemReal) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações do Item -->
                            <div class="flex items-center gap-1">
                                <button type="button" data-modal-open="modal-edit-item-<?= $it['id'] ?>" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Editar">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <form action="../actions/item_delete.php" method="POST" class="inline">
                                    <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                                    <input type="hidden" name="acao" value="lixeira">
                                    <button type="submit" data-confirm="Mover '<?= clean($it['titulo']) ?>' para a lixeira do histórico?" data-confirm-title="Excluir Item" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Excluir">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Modal de Edição para este item -->
                        <?php include __DIR__ . '/partials/modal_item_edit.php'; ?>
                    <?php endforeach; ?>
                </div>

            <!-- ============================================== -->
            <!-- RENDERIZAÇÃO: CATEGORIA FILMES & SÉRIES -->
            <!-- ============================================== -->
            <?php elseif ($lista['tipo'] === 'filmes'): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($itens as $it): ?>
                        <div data-item-card class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between <?= $it['concluido'] ? 'opacity-80 bg-slate-50' : '' ?>">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <?php if (!empty($it['genero'])): ?>
                                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-100">
                                            <?= clean($it['genero']) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($it['plataforma'])): ?>
                                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <i data-lucide="tv" class="w-3 h-3 inline mr-1"></i><?= clean($it['plataforma']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="item-title text-base font-bold <?= $it['concluido'] ? 'line-through text-slate-400' : 'text-slate-900' ?> mb-1">
                                    <?= clean($it['titulo']) ?>
                                </h3>

                                <?php if (!empty($it['nota'])): ?>
                                    <div class="flex items-center gap-1 text-amber-400 text-xs mb-2">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i data-lucide="star" class="w-3.5 h-3.5 <?= $s <= $it['nota'] ? 'fill-amber-400 text-amber-400' : 'text-slate-200' ?>"></i>
                                        <?php endfor; ?>
                                        <span class="text-slate-500 text-[11px] ml-1">(<?= (int)$it['nota'] ?>/5)</span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($it['notas'])): ?>
                                    <p class="text-xs text-slate-500 italic bg-slate-50 p-2 rounded-xl mb-3">
                                        "<?= clean($it['notas']) ?>"
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                    <input type="checkbox" 
                                           class="item-checkbox w-4 h-4 text-rose-600 rounded-md border-slate-300 focus:ring-rose-500 cursor-pointer"
                                           <?= $it['concluido'] ? 'checked' : '' ?>
                                           onchange="toggleItemStatus(this, <?= $it['id'] ?>, '../actions/item_toggle.php')">
                                    <span><?= $it['concluido'] ? 'Assistido' : 'Para Assistir' ?></span>
                                </label>

                                <div class="flex items-center gap-1">
                                    <button type="button" data-modal-open="modal-edit-item-<?= $it['id'] ?>" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form action="../actions/item_delete.php" method="POST" class="inline">
                                        <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                                        <input type="hidden" name="acao" value="lixeira">
                                        <button type="submit" data-confirm="Excluir filme '<?= clean($it['titulo']) ?>'?" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php include __DIR__ . '/partials/modal_item_edit.php'; ?>
                    <?php endforeach; ?>
                </div>

            <!-- ============================================== -->
            <!-- RENDERIZAÇÃO: CATEGORIA RECEITAS -->
            <!-- ============================================== -->
            <?php elseif ($lista['tipo'] === 'receitas'): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php foreach ($itens as $it): ?>
                        <div data-item-card class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-2">
                                        <?php if (!empty($it['rendimento'])): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                <i data-lucide="pie-chart" class="w-3.5 h-3.5"></i>
                                                <span><?= clean($it['rendimento']) ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($it['tempo_preparo'])): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-orange-50 text-orange-800 border border-orange-200">
                                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                                <span><?= clean($it['tempo_preparo']) ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex items-center gap-1">
                                        <button type="button" data-modal-open="modal-edit-item-<?= $it['id'] ?>" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <form action="../actions/item_delete.php" method="POST" class="inline">
                                            <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                                            <input type="hidden" name="acao" value="lixeira">
                                            <button type="submit" data-confirm="Excluir receita '<?= clean($it['titulo']) ?>'?" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <h3 class="text-xl font-extrabold text-slate-900 mb-4">
                                    <?= clean($it['titulo']) ?>
                                </h3>

                                <!-- Ingredientes -->
                                <?php if (!empty($it['ingredientes'])): ?>
                                    <div class="mb-4">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-1.5">
                                            <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-600"></i>
                                            <span>Ingredientes</span>
                                        </h4>
                                        <div class="bg-amber-50/40 border border-amber-100 rounded-2xl p-4 text-xs text-slate-700 whitespace-pre-line leading-relaxed font-mono">
                                            <?= clean($it['ingredientes']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Modo de Preparo -->
                                <?php if (!empty($it['modo_preparo'])): ?>
                                    <div class="mb-4">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-1.5">
                                            <i data-lucide="chef-hat" class="w-4 h-4 text-amber-600"></i>
                                            <span>Modo de Preparo</span>
                                        </h4>
                                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 text-xs text-slate-700 whitespace-pre-line leading-relaxed">
                                            <?= clean($it['modo_preparo']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                    <input type="checkbox" 
                                           class="item-checkbox w-4 h-4 text-amber-600 rounded-md border-slate-300 focus:ring-amber-500 cursor-pointer"
                                           <?= $it['concluido'] ? 'checked' : '' ?>
                                           onchange="toggleItemStatus(this, <?= $it['id'] ?>, '../actions/item_toggle.php')">
                                    <span><?= $it['concluido'] ? 'Feita e Aprovada! 😋' : 'Quero preparar' ?></span>
                                </label>
                            </div>
                        </div>

                        <?php include __DIR__ . '/partials/modal_item_edit.php'; ?>
                    <?php endforeach; ?>
                </div>

            <!-- ============================================== -->
            <!-- RENDERIZAÇÃO: TAREFAS / VIAGENS / OUTROS -->
            <!-- ============================================== -->
            <?php else: ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm divide-y divide-slate-100 overflow-hidden">
                    <?php foreach ($itens as $it): 
                        $priorityColors = [
                            'urgente' => 'bg-rose-100 text-rose-800 border-rose-200',
                            'alta' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'media' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'baixa' => 'bg-slate-100 text-slate-600 border-slate-200'
                        ];
                        $badgeClass = $priorityColors[$it['prioridade']] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                    ?>
                        <div data-item-card class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition <?= $it['concluido'] ? 'bg-slate-50/70' : '' ?>">
                            <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                <label class="relative flex items-center justify-center cursor-pointer">
                                    <input type="checkbox" 
                                           class="item-checkbox w-5 h-5 text-indigo-600 rounded-lg border-slate-300 focus:ring-indigo-500 cursor-pointer"
                                           <?= $it['concluido'] ? 'checked' : '' ?>
                                           onchange="toggleItemStatus(this, <?= $it['id'] ?>, '../actions/item_toggle.php')">
                                </label>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="item-title font-semibold text-sm <?= $it['concluido'] ? 'line-through text-slate-400' : 'text-slate-800' ?>">
                                            <?= clean($it['titulo']) ?>
                                        </span>

                                        <?php if (!empty($it['prioridade'])): ?>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border <?= $badgeClass ?>">
                                                <?= $it['prioridade'] ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($it['data_limite'])): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] text-slate-500">
                                                <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                                <span><?= formatDate($it['data_limite']) ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($it['notas'])): ?>
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-1">
                                            <?= clean($it['notas']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <button type="button" data-modal-open="modal-edit-item-<?= $it['id'] ?>" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg transition" title="Editar">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <form action="../actions/item_delete.php" method="POST" class="inline">
                                    <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                                    <input type="hidden" name="acao" value="lixeira">
                                    <button type="submit" data-confirm="Mover '<?= clean($it['titulo']) ?>' para a lixeira do histórico?" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition" title="Excluir">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <?php include __DIR__ . '/partials/modal_item_edit.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

</div>

<!-- ============================================== -->
<!-- MODAL: ADICIONAR NOVO ITEM (Adaptado por Categoria) -->
<!-- ============================================== -->
<div id="modal-adicionar-item" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 sm:p-7 animate-modal max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl <?= $catMeta['bg_light'] ?> <?= $catMeta['text_color'] ?> flex items-center justify-center">
                    <i data-lucide="<?= $catMeta['icone'] ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Adicionar à Lista</h3>
                    <p class="text-xs text-slate-500"><?= $catMeta['nome'] ?> - <?= clean($lista['titulo']) ?></p>
                </div>
            </div>
            <button type="button" data-modal-close="modal-adicionar-item" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/item_create.php" method="POST" class="space-y-4">
            <input type="hidden" name="lista_id" value="<?= $lista['id'] ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_titulo">
                    <?= $lista['tipo'] === 'filmes' ? 'Título do Filme / Série *' : ($lista['tipo'] === 'receitas' ? 'Nome do Prato / Receita *' : 'Nome do Item *') ?>
                </label>
                <input type="text" id="item_titulo" name="titulo" required placeholder="Digite o nome..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <!-- Campos específicos para SUPERMERCADO -->
            <?php if ($lista['tipo'] === 'supermercado'): ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_quant">Quantidade</label>
                        <input type="number" step="0.01" min="0.01" id="item_quant" name="quantidade" value="1" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_unid">Unidade</label>
                        <select id="item_unid" name="unidade" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="un">un (unidade)</option>
                            <option value="kg">kg (quilos)</option>
                            <option value="g">g (gramas)</option>
                            <option value="litros">litros</option>
                            <option value="pct">pct (pacote)</option>
                            <option value="cx">cx (caixa)</option>
                            <option value="lata">lata</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_preco_est">Preço Estimado (R$)</label>
                        <input type="number" step="0.01" min="0" id="item_preco_est" name="preco_estimado" placeholder="0.00" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_preco_real">Preço Real Pago (R$)</label>
                        <input type="number" step="0.01" min="0" id="item_preco_real" name="preco_real" placeholder="0.00" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

            <!-- Campos específicos para FILMES/SÉRIES -->
            <?php elseif ($lista['tipo'] === 'filmes'): ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_genero">Gênero</label>
                        <input type="text" id="item_genero" name="genero" placeholder="Ex: Ficção, Comédia..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_plataforma">Onde Assistir</label>
                        <input type="text" id="item_plataforma" name="plataforma" placeholder="Ex: Netflix, Prime..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_nota">Avaliação (1 a 5 estrelas)</label>
                    <select id="item_nota" name="nota" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">Ainda não avaliado</option>
                        <option value="5">⭐⭐⭐⭐⭐ 5 Estrelas - Excelente</option>
                        <option value="4">⭐⭐⭐⭐ 4 Estrelas - Muito Bom</option>
                        <option value="3">⭐⭐⭐ 3 Estrelas - Bom</option>
                        <option value="2">⭐⭐ 2 Estrelas - Regular</option>
                        <option value="1">⭐ 1 Estrela - Ruim</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_notas_filme">Anotações / Resenha</label>
                    <textarea id="item_notas_filme" name="notas" rows="2" placeholder="Comentários sobre o filme..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

            <!-- Campos específicos para RECEITAS -->
            <?php elseif ($lista['tipo'] === 'receitas'): ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_rendimento">Rendimento</label>
                        <input type="text" id="item_rendimento" name="rendimento" placeholder="Ex: 6 porções" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_tempo">Tempo de Preparo</label>
                        <input type="text" id="item_tempo" name="tempo_preparo" placeholder="Ex: 45 min" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_ingredientes">Ingredientes (um por linha)</label>
                    <textarea id="item_ingredientes" name="ingredientes" rows="3" placeholder="- 2 xícaras de farinha&#10;- 3 ovos&#10;- 1 colher de manteiga" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_preparo">Modo de Preparo</label>
                    <textarea id="item_preparo" name="modo_preparo" rows="3" placeholder="1. Misture os ingredientes secos...&#10;2. Asse por 30 minutos a 180°C..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

            <!-- Campos para TAREFAS / VIAGENS / OUTROS -->
            <?php else: ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_prioridade">Prioridade</label>
                        <select id="item_prioridade" name="prioridade" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="baixa">Baixa</option>
                            <option value="media" selected>Média</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_data_limite">Data Limite / Prazo</label>
                        <input type="date" id="item_data_limite" name="data_limite" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="item_notas_geral">Observações (Opcional)</label>
                    <textarea id="item_notas_geral" name="notas" rows="2" placeholder="Detalhes adicionais..." class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>
            <?php endif; ?>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" data-modal-close="modal-adicionar-item" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Salvar Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: EDITAR CONFIGURAÇÕES DA LISTA -->
<!-- ============================================== -->
<?php if ($perm['can_edit']): ?>
<div id="modal-editar-lista" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 animate-modal max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900">Configurações da Lista</h3>
            <button type="button" data-modal-close="modal-editar-lista" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/list_edit.php" method="POST" class="space-y-4">
            <input type="hidden" name="lista_id" value="<?= $lista['id'] ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Título da Lista</label>
                <input type="text" name="titulo" value="<?= clean($lista['titulo']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Categoria / Tipo</label>
                <select name="tipo" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="supermercado" <?= $lista['tipo'] === 'supermercado' ? 'selected' : '' ?>>Supermercado</option>
                    <option value="filmes" <?= $lista['tipo'] === 'filmes' ? 'selected' : '' ?>>Filmes & Séries</option>
                    <option value="receitas" <?= $lista['tipo'] === 'receitas' ? 'selected' : '' ?>>Receitas</option>
                    <option value="tarefas" <?= $lista['tipo'] === 'tarefas' ? 'selected' : '' ?>>Tarefas & Rotina</option>
                    <option value="viagens" <?= $lista['tipo'] === 'viagens' ? 'selected' : '' ?>>Viagens</option>
                    <option value="outros" <?= $lista['tipo'] === 'outros' ? 'selected' : '' ?>>Outros</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Grupo Vinculado</label>
                <select name="grupo_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">🔒 Lista Privada (Apenas Você)</option>
                    <?php foreach ($meusGrupos as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= (int)$lista['grupo_id'] === (int)$g['id'] ? 'selected' : '' ?>>👥 <?= clean($g['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Descrição</label>
                <textarea name="descricao" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= clean($lista['descricao']) ?></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" data-modal-close="modal-editar-lista" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md transition">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
