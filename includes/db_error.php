<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listo - Configuração do Banco de Dados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl border border-slate-100 p-6 md:p-8">
        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-center text-slate-900 mb-2">Conexão com o Banco de Dados</h1>
        <p class="text-sm text-slate-600 text-center mb-6">
            Não foi possível conectar ao banco de dados MySQL com as configurações atuais.
        </p>

        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl p-3 mb-6 font-mono break-all">
            <?= htmlspecialchars($errorMsg ?? 'Erro desconhecido ao conectar com MySQL'); ?>
        </div>

        <div class="space-y-4 text-sm text-slate-600">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <h2 class="font-semibold text-slate-800 mb-2">Como resolver:</h2>
                <ol class="list-decimal list-inside space-y-1.5 text-xs text-slate-600">
                    <li>Verifique se o seu serviço MySQL (XAMPP / Wamp / MariaDB) está iniciado.</li>
                    <li>Abra o arquivo <code class="bg-slate-200 px-1 py-0.5 rounded text-indigo-700 font-mono">bd.php</code> e confira host, usuário e senha.</li>
                    <li>Execute o instalador automático para criar o banco e as tabelas com dados de teste.</li>
                </ol>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-3">
                <a href="install.php" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl text-center shadow transition">
                    Abrir Instalador Automático
                </a>
                <button onclick="window.location.reload()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition">
                    Tentar Novamente
                </button>
            </div>
        </div>
    </div>
</body>
</html>
