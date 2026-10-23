<?php
$pageTitle = 'Criar Nova Conta';
require_once __DIR__ . '/../includes/header.php';

// Se já estiver logado, redireciona para o painel
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="min-h-[85vh] flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-7 sm:p-9">
        
        <!-- Logotipo e Cabeçalho -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-200 mb-3">
                <i data-lucide="sparkles" class="w-8 h-8 stroke-[2.5]"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Crie sua Conta</h1>
            <p class="text-sm text-slate-500 mt-1">Comece a organizar suas listas e tarefas em minutos.</p>
        </div>

        <!-- Formulário de Cadastro -->
        <form action="../actions/auth_register.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="nome">Nome Completo</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </span>
                    <input type="text" id="nome" name="nome" required placeholder="Como prefere ser chamado?" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="email">E-mail Único</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" id="email" name="email" required placeholder="seu.melhor@email.com" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Cada conta possui um único e-mail individual seguro.</span>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="senha">Senha</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" id="senha" name="senha" required minlength="6" placeholder="Mínimo de 6 caracteres" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="senha_confirmar">Confirmar Senha</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </span>
                    <input type="password" id="senha_confirmar" name="senha_confirmar" required minlength="6" placeholder="Repita sua senha" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-3.5 px-4 rounded-2xl shadow-lg shadow-indigo-100 hover:shadow-xl transition transform active:scale-98 flex items-center justify-center gap-2">
                    <span>Criar Minha Conta</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </form>

        <!-- Rodapé do Card -->
        <p class="text-center text-xs text-slate-500 mt-6">
            Já possui uma conta? 
            <a href="login.php" class="font-bold text-indigo-600 hover:text-indigo-700 hover:underline">Fazer login</a>
        </p>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
