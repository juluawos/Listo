    </main>

    <?php if ($user): ?>
    <!-- Mobile Bottom Navigation Bar (Fixo na base para Mobile First) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-slate-200 px-3 py-2 flex items-center justify-around shadow-lg">
        <a href="<?= $base ?>views/dashboard.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition <?= $currentScript === 'dashboard.php' ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
            <i data-lucide="layout-grid" class="w-5 h-5"></i>
            <span class="text-[10px]">Início</span>
        </a>

        <a href="<?= $base ?>views/lists.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition <?= $currentScript === 'lists.php' || $currentScript === 'list_view.php' ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
            <i data-lucide="list-todo" class="w-5 h-5"></i>
            <span class="text-[10px]">Listas</span>
        </a>

        <a href="<?= $base ?>views/groups.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition <?= $currentScript === 'groups.php' || $currentScript === 'group_view.php' ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span class="text-[10px]">Grupos</span>
        </a>

        <a href="<?= $base ?>views/history.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition <?= $currentScript === 'history.php' ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
            <i data-lucide="archive" class="w-5 h-5"></i>
            <span class="text-[10px]">Lixeira</span>
        </a>

        <a href="<?= $base ?>views/profile.php" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl transition <?= $currentScript === 'profile.php' ? 'text-indigo-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span class="text-[10px]">Perfil</span>
        </a>
    </nav>
    <?php endif; ?>

    <!-- Container dos Toasts e Modais -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full px-4 sm:px-0 pointer-events-none"></div>
    <div id="listo-modal-overlay" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 transition-opacity"></div>

    <!-- Scripts da Aplicação -->
    <script src="<?= $base ?>assets/js/app.js"></script>

    <script>
        // Inicializar ícones Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Exibir Flash Message via Toast se existir
        <?php
        $flash = getFlash();
        if ($flash):
        ?>
        document.addEventListener('DOMContentLoaded', () => {
            ListoToast.show(
                <?= json_encode($flash['message']); ?>, 
                <?= json_encode($flash['type']); ?>
            );
        });
        <?php endif; ?>
    </script>
</body>
</html>
