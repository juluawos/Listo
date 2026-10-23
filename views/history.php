<?php
$pageTitle = 'Histórico & Lixeira';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = $_SESSION['usuario_id'];
$pdo = getDB();

// 1. Listas na Lixeira
$stmtListas = $pdo->prepare("
    SELECT l.*, 
           u.nome as criador_nome,
           g.nome as grupo_nome,
           (SELECT COUNT(*) FROM itens_lista WHERE lista_id = l.id) as total_itens
    FROM listas l
    JOIN usuarios u ON l.usuario_id = u.id
    LEFT JOIN grupos g ON l.grupo_id = g.id
    WHERE l.status = 'lixeira' AND l.usuario_id = ?
    ORDER BY l.atualizado_em DESC
");
$stmtListas->execute([$userId]);
$listasLixeira = $stmtListas->fetchAll();

// 2. Itens individuais na Lixeira
$stmtItens = $pdo->prepare("
    SELECT il.*, l.titulo as lista_titulo, l.tipo as lista_tipo
    FROM itens_lista il
    JOIN listas l ON il.lista_id = l.id
    WHERE il.status = 'lixeira' AND l.usuario_id = ?
    ORDER BY il.atualizado_em DESC
    LIMIT 30
");
$stmtItens->execute([$userId]);
$itensLixeira = $stmtItens->fetchAll();
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Topo -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i data-lucide="archive" class="w-8 h-8 text-amber-500"></i>
                <span>Histórico & Lixeira</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Recupere listas apagadas ou reutilize itens antigos com um clique.
            </p>
        </div>
    </div>

    <!-- Seção: Listas na Lixeira -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="trash-2" class="w-5 h-5 text-rose-500"></i>
                <span>Listas na Lixeira (<?= count($listasLixeira) ?>)</span>
            </h2>
        </div>

        <?php if (empty($listasLixeira)): ?>
            <div class="text-center py-8 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <div class="w-10 h-10 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center mx-auto mb-2">
                    <i data-lucide="check" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-semibold text-slate-600">A lixeira de listas está vazia!</p>
                <p class="text-[11px] text-slate-400">Nenhuma lista foi descartada recentemente.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($listasLixeira as $l): 
                    $catMeta = getCategoryMeta($l['tipo']);
                ?>
                    <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl <?= $catMeta['bg_light'] ?> <?= $catMeta['text_color'] ?> flex items-center justify-center font-bold text-sm shrink-0">
                                <i data-lucide="<?= $catMeta['icone'] ?>" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800"><?= clean($l['titulo']) ?></h3>
                                <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                                    <span><?= $catMeta['nome'] ?></span>
                                    <span>&bull;</span>
                                    <span><?= $l['total_itens'] ?> itens</span>
                                    <span>&bull;</span>
                                    <span>Descartada em <?= formatDate($l['atualizado_em']) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <!-- Restaurar -->
                            <form action="../actions/list_delete.php" method="POST" class="inline">
                                <input type="hidden" name="lista_id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="acao" value="restaurar">
                                <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shadow-sm">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                    <span>Restaurar Lista</span>
                                </button>
                            </form>

                            <!-- Excluir Permanentemente -->
                            <form action="../actions/list_delete.php" method="POST" class="inline">
                                <input type="hidden" name="lista_id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="acao" value="excluir_permanente">
                                <button type="submit" data-confirm="ATENÇÃO: A lista '<?= clean($l['titulo']) ?>' e todos os seus itens serão excluídos para sempre. Esta ação não pode ser desfeita!" data-confirm-title="Excluir Definitivamente" data-confirm-color="rose" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Excluir Definitivamente">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Seção: Itens Individuais Descartados -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="check-square" class="w-5 h-5 text-indigo-600"></i>
                <span>Itens Excluídos do Histórico (<?= count($itensLixeira) ?>)</span>
            </h2>
        </div>

        <?php if (empty($itensLixeira)): ?>
            <div class="text-center py-8 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <p class="text-xs font-semibold text-slate-600">Nenhum item descartado no momento.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach ($itensLixeira as $it): ?>
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-sm font-semibold text-slate-800 block"><?= clean($it['titulo']) ?></span>
                            <span class="text-xs text-slate-400">Pertence à lista: <strong><?= clean($it['lista_titulo']) ?></strong></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <form action="../actions/item_delete.php" method="POST" class="inline">
                                <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                                <input type="hidden" name="acao" value="restaurar">
                                <button type="submit" class="bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 font-bold text-xs px-3 py-1.5 rounded-xl transition flex items-center gap-1">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                    <span>Restaurar</span>
                                </button>
                            </form>

                            <form action="../actions/item_delete.php" method="POST" class="inline">
                                <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                                <input type="hidden" name="acao" value="excluir_permanente">
                                <button type="submit" data-confirm="Excluir item permanentemente?" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
