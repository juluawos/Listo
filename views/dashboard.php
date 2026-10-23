<?php
$pageTitle = 'Painel de Controle';
require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = $_SESSION['usuario_id'];
$pdo = getDB();

// 1. Estatísticas do Usuário
// Total de listas ativas acessíveis
$stmtListasCount = $pdo->prepare("
    SELECT COUNT(DISTINCT l.id) 
    FROM listas l
    LEFT JOIN membros_grupo mg ON l.grupo_id = mg.grupo_id
    WHERE l.status = 'ativa' 
      AND (l.usuario_id = ? OR mg.usuario_id = ?)
");
$stmtListasCount->execute([$userId, $userId]);
$totalListas = (int)$stmtListasCount->fetchColumn();

// Itens concluídos
$stmtItensConcluidos = $pdo->prepare("
    SELECT COUNT(DISTINCT il.id) 
    FROM itens_lista il
    JOIN listas l ON il.lista_id = l.id
    LEFT JOIN membros_grupo mg ON l.grupo_id = mg.grupo_id
    WHERE il.concluido = 1 
      AND il.status = 'ativo'
      AND l.status = 'ativa'
      AND (l.usuario_id = ? OR mg.usuario_id = ?)
");
$stmtItensConcluidos->execute([$userId, $userId]);
$totalItensConcluidos = (int)$stmtItensConcluidos->fetchColumn();

// Grupos
$stmtGruposCount = $pdo->prepare("SELECT COUNT(*) FROM membros_grupo WHERE usuario_id = ?");
$stmtGruposCount->execute([$userId]);
$totalGrupos = (int)$stmtGruposCount->fetchColumn();

// Lixeira
$stmtLixeiraCount = $pdo->prepare("SELECT COUNT(*) FROM listas WHERE usuario_id = ? AND status = 'lixeira'");
$stmtLixeiraCount->execute([$userId]);
$totalLixeira = (int)$stmtLixeiraCount->fetchColumn();

// 2. Buscar grupos do usuário (para o modal de criar lista e listagem)
$stmtMeusGrupos = $pdo->prepare("
    SELECT g.id, g.nome, g.codigo_convite, mg.papel 
    FROM grupos g
    JOIN membros_grupo mg ON g.id = mg.grupo_id
    WHERE mg.usuario_id = ?
    ORDER BY g.nome ASC
");
$stmtMeusGrupos->execute([$userId]);
$meusGrupos = $stmtMeusGrupos->fetchAll();

// 3. Buscar listas recentes acessíveis
$stmtListasRecentes = $pdo->prepare("
    SELECT DISTINCT l.*, 
           g.nome as grupo_nome,
           (SELECT COUNT(*) FROM itens_lista WHERE lista_id = l.id AND status = 'ativo') as total_itens,
           (SELECT COUNT(*) FROM itens_lista WHERE lista_id = l.id AND concluido = 1 AND status = 'ativo') as itens_concluidos
    FROM listas l
    LEFT JOIN membros_grupo mg ON l.grupo_id = mg.grupo_id
    LEFT JOIN grupos g ON l.grupo_id = g.id
    WHERE l.status = 'ativa' 
      AND (l.usuario_id = ? OR mg.usuario_id = ?)
    ORDER BY l.atualizado_em DESC
    LIMIT 6
");
$stmtListasRecentes->execute([$userId, $userId]);
$listasRecentes = $stmtListasRecentes->fetchAll();

$categories = getCategoryMeta();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

    <!-- Banner de Boas-vindas -->
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-semibold mb-3">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
                <span>Plataforma Pronto para Uso</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Olá, <?= clean($user['nome']); ?>! 👋
            </h1>
            <p class="text-indigo-100 text-sm sm:text-base mt-1 max-w-xl">
                Crie listas práticas para supermercado, receitas, filmes, viagens e tarefas do seu dia a dia.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <button type="button" data-modal-open="modal-nova-lista" class="flex-1 sm:flex-initial bg-white text-indigo-700 hover:bg-indigo-50 font-bold px-5 py-3 rounded-2xl shadow-lg transition flex items-center justify-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                <span>Nova Lista</span>
            </button>
            <button type="button" data-modal-open="modal-entrar-grupo" class="flex-1 sm:flex-initial bg-indigo-500/40 hover:bg-indigo-500/60 text-white font-semibold px-4 py-3 rounded-2xl border border-white/20 backdrop-blur-sm transition flex items-center justify-center gap-2">
                <i data-lucide="key" class="w-4 h-4"></i>
                <span>Entrar em Grupo</span>
            </button>
        </div>
    </div>

    <!-- Indicadores / Cards de Estatísticas -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <i data-lucide="list-todo" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500">Listas Ativas</span>
                <p class="text-xl sm:text-2xl font-bold text-slate-900"><?= $totalListas ?></p>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500">Concluídos</span>
                <p class="text-xl sm:text-2xl font-bold text-slate-900"><?= $totalItensConcluidos ?></p>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500">Grupos / Família</span>
                <p class="text-xl sm:text-2xl font-bold text-slate-900"><?= $totalGrupos ?></p>
            </div>
        </div>

        <a href="history.php" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3.5 hover:border-indigo-300 transition group">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                <i data-lucide="archive" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500">Lixeira</span>
                <p class="text-xl sm:text-2xl font-bold text-slate-900"><?= $totalLixeira ?></p>
            </div>
        </a>
    </div>

    <!-- Categorias Visuais Inteligentes (Quick access) -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="grid" class="w-5 h-5 text-indigo-600"></i>
                <span>Categorias de Listas</span>
            </h2>
            <a href="lists.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                Ver todas <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <?php foreach ($categories as $catKey => $cat): ?>
                <a href="lists.php?tipo=<?= $catKey ?>" class="bg-white p-4 rounded-2xl border border-slate-200/70 shadow-sm hover:border-indigo-300 hover:shadow-md transition group text-center flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl <?= $cat['bg_light'] ?> <?= $cat['text_color'] ?> flex items-center justify-center mb-2.5 group-hover:scale-110 transition shadow-sm">
                        <i data-lucide="<?= $cat['icone'] ?>" class="w-6 h-6"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-800"><?= $cat['nome'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Listas Recentes -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="clock" class="w-5 h-5 text-indigo-600"></i>
                <span>Listas Recentes</span>
            </h2>
            <a href="lists.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                Explorar todas <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>

        <?php if (empty($listasRecentes)): ?>
            <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-8 text-center">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="plus-circle" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Nenhuma lista ativa encontrada</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">Crie sua primeira lista de compras, tarefas ou filmes para começar a organizar sua rotina.</p>
                <button type="button" data-modal-open="modal-nova-lista" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-4 py-2.5 rounded-xl transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Criar Lista Agora
                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($listasRecentes as $lista): 
                    $catMeta = getCategoryMeta($lista['tipo']);
                    $pct = $lista['total_itens'] > 0 ? round(($lista['itens_concluidos'] / $lista['total_itens']) * 100) : 0;
                ?>
                    <a href="list_view.php?id=<?= $lista['id'] ?>" class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex flex-col justify-between group">
                        <div>
                            <!-- Cabeçalho do Card -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold <?= $catMeta['bg_light'] ?> <?= $catMeta['text_color'] ?>">
                                    <i data-lucide="<?= $catMeta['icone'] ?>" class="w-3.5 h-3.5"></i>
                                    <span><?= $catMeta['nome'] ?></span>
                                </span>

                                <?php if (!empty($lista['grupo_nome'])): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                        <i data-lucide="users" class="w-3 h-3"></i>
                                        <span class="truncate max-w-[100px]"><?= clean($lista['grupo_nome']) ?></span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Privada
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition mb-1">
                                <?= clean($lista['titulo']) ?>
                            </h3>
                            
                            <?php if (!empty($lista['descricao'])): ?>
                                <p class="text-xs text-slate-500 line-clamp-2 mb-3">
                                    <?= clean($lista['descricao']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Barra de Progresso e Rodapé -->
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                                <span><?= $lista['itens_concluidos'] ?> de <?= $lista['total_itens'] ?> itens</span>
                                <span class="font-bold text-slate-700"><?= $pct ?>%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Grupos / Famílias do Usuário -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
                    <span>Seus Grupos de Compartilhamento</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Compartilhe listas com sua família, equipe ou amigos em tempo real.</p>
            </div>
            <a href="groups.php" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                Gerenciar grupos &rarr;
            </a>
        </div>

        <?php if (empty($meusGrupos)): ?>
            <div class="text-center py-6">
                <p class="text-sm text-slate-500 mb-3">Você ainda não participa de nenhum grupo.</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="groups.php" class="text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold px-4 py-2 rounded-xl transition">
                        Criar Grupo de Família
                    </a>
                    <button type="button" data-modal-open="modal-entrar-grupo" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl transition">
                        Digitar Código de Convite
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php foreach ($meusGrupos as $g): ?>
                    <a href="group_view.php?id=<?= $g['id'] ?>" class="p-4 rounded-2xl bg-slate-50 hover:bg-indigo-50/50 border border-slate-200 hover:border-indigo-200 transition flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white text-indigo-600 border border-slate-200 flex items-center justify-center font-bold text-sm shadow-sm">
                                <?= getInitials($g['nome']) ?>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-700"><?= clean($g['nome']) ?></h4>
                                <span class="text-[11px] text-slate-400 capitalize"><?= $g['papel'] === 'admin' ? 'Administrador' : 'Membro' ?></span>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

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
                    <p class="text-xs text-slate-500">Escolha o tipo e personalize como desejar</p>
                </div>
            </div>
            <button type="button" data-modal-close="modal-nova-lista" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/list_create.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="titulo">Título da Lista *</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Ex: Compras de Domingo, Filmes para Ver..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="tipo">Categoria / Tipo de Lista *</label>
                <select id="tipo" name="tipo" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="supermercado">🛒 Supermercado (com quant., unidade e preços)</option>
                    <option value="filmes">🎬 Filmes & Séries (streaming, gênero, notas)</option>
                    <option value="receitas">🍳 Receitas (ingredientes, rendimento, preparo)</option>
                    <option value="tarefas" selected>✅ Tarefas & Rotina (prazos, prioridades)</option>
                    <option value="viagens">✈️ Viagens & Bagagem (checklist completo)</option>
                    <option value="outros">📝 Outros (personalizada)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="grupo_id">Compartilhar com Grupo / Família</label>
                <select id="grupo_id" name="grupo_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">🔒 Lista Privada (Apenas Você)</option>
                    <?php foreach ($meusGrupos as $g): ?>
                        <option value="<?= $g['id'] ?>">👥 <?= clean($g['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1" for="descricao">Descrição ou Observação (Opcional)</label>
                <textarea id="descricao" name="descricao" rows="2" placeholder="Alguma instrução especial para esta lista..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
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

<!-- MODAL: ENTRAR EM GRUPO COM CÓDIGO -->
<div id="modal-entrar-grupo" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 animate-modal">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center">
                    <i data-lucide="key" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Entrar em um Grupo</h3>
            </div>
            <button type="button" data-modal-close="modal-entrar-grupo" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <p class="text-sm text-slate-600 mb-4">
            Digite o código de convite (ex: <code>LISTO-XXXXXX</code>) enviado por quem criou o grupo para começar a colaborar.
        </p>

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
