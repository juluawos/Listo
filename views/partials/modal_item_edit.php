<?php
/**
 * Listo - Modal de Edição de Item Específico
 * Reutilizado no loop de itens em list_view.php
 */
?>
<div id="modal-edit-item-<?= $it['id'] ?>" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-lg w-full p-6 animate-modal max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-900">Editar Item</h3>
            <button type="button" data-modal-close="modal-edit-item-<?= $it['id'] ?>" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="../actions/item_edit.php" method="POST" class="space-y-4">
            <input type="hidden" name="item_id" value="<?= $it['id'] ?>">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Título / Nome *</label>
                <input type="text" name="titulo" value="<?= clean($it['titulo']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <?php if ($lista['tipo'] === 'supermercado'): ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Quantidade</label>
                        <input type="number" step="0.01" min="0.01" name="quantidade" value="<?= (float)$it['quantidade'] ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Unidade</label>
                        <select name="unidade" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <?php foreach (['un', 'kg', 'g', 'litros', 'pct', 'cx', 'lata'] as $u): ?>
                                <option value="<?= $u ?>" <?= $it['unidade'] === $u ? 'selected' : '' ?>><?= $u ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Preço Estimado (R$)</label>
                        <input type="number" step="0.01" min="0" name="preco_estimado" value="<?= (float)$it['preco_estimado'] ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Preço Real (R$)</label>
                        <input type="number" step="0.01" min="0" name="preco_real" value="<?= (float)$it['preco_real'] ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

            <?php elseif ($lista['tipo'] === 'filmes'): ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Gênero</label>
                        <input type="text" name="genero" value="<?= clean($it['genero']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Onde Assistir</label>
                        <input type="text" name="plataforma" value="<?= clean($it['plataforma']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Avaliação (1 a 5 estrelas)</label>
                    <select name="nota" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">Sem avaliação</option>
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <option value="<?= $s ?>" <?= (int)$it['nota'] === $s ? 'selected' : '' ?>><?= str_repeat('⭐', $s) ?> (<?= $s ?> Estrelas)</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Anotações / Resenha</label>
                    <textarea name="notas" rows="2" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= clean($it['notas']) ?></textarea>
                </div>

            <?php elseif ($lista['tipo'] === 'receitas'): ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rendimento</label>
                        <input type="text" name="rendimento" value="<?= clean($it['rendimento']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tempo de Preparo</label>
                        <input type="text" name="tempo_preparo" value="<?= clean($it['tempo_preparo']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Ingredientes</label>
                    <textarea name="ingredientes" rows="3" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= clean($it['ingredientes']) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Modo de Preparo</label>
                    <textarea name="modo_preparo" rows="3" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= clean($it['modo_preparo']) ?></textarea>
                </div>

            <?php else: ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Prioridade</label>
                        <select name="prioridade" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="baixa" <?= $it['prioridade'] === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                            <option value="media" <?= $it['prioridade'] === 'media' ? 'selected' : '' ?>>Média</option>
                            <option value="alta" <?= $it['prioridade'] === 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="urgente" <?= $it['prioridade'] === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Data Limite</label>
                        <input type="date" name="data_limite" value="<?= clean($it['data_limite']) ?>" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Observações</label>
                    <textarea name="notas" rows="2" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= clean($it['notas']) ?></textarea>
                </div>
            <?php endif; ?>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" data-modal-close="modal-edit-item-<?= $it['id'] ?>" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium text-sm transition">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md transition">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
