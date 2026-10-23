<?php
$pageTitle = 'Entrar na sua conta';
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
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-200 mb-3">
                <i data-lucide="check-check" class="w-8 h-8 stroke-[2.5]"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Bem-vindo ao Listo</h1>
            <p class="text-sm text-slate-500 mt-1">Sua rotina, compras e tarefas em perfeita harmonia.</p>
        </div>

        <!-- Formulário de Login -->
        <form action="../actions/auth_login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="email">E-mail</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700" for="senha">Senha</label>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" id="senha" name="senha" required placeholder="Sua senha secreta" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-3.5 px-4 rounded-2xl shadow-lg shadow-indigo-100 hover:shadow-xl transition transform active:scale-98 flex items-center justify-center gap-2">
                    <span>Entrar no Listo</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </form>

        <!-- Botão de Preenchimento Rápido com Conta Demo -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <button type="button" onclick="preencherDemo()" class="w-full text-xs text-indigo-600 hover:text-indigo-800 bg-indigo-50/60 hover:bg-indigo-100/60 font-semibold py-2.5 px-3 rounded-xl border border-indigo-100 transition flex items-center justify-center gap-1.5">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-500"></i>
                <span>Preencher com Usuário Demo (demo@listo.com)</span>
            </button>
        </div>

        <!-- Rodapé do Card -->
        <p class="text-center text-xs text-slate-500 mt-6">
            Não tem uma conta ainda? 
            <a href="register.php" class="font-bold text-indigo-600 hover:text-indigo-700 hover:underline">Cadastre-se gratuitamente</a>
        </p>

    </div>
</div>

<script>
function preencherDemo() {
    document.getElementById('email').value = 'demo@listo.com';
    document.getElementById('senha').value = '123456';
    ListoToast.show('Credenciais de teste preenchidas!', 'info');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
