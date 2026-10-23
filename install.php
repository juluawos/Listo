<?php
/**
 * Listo - Instalador e Inicializador Automático do Banco de Dados
 */

$status = null;
$message = '';
$details = [];

// Credenciais padrão ou vindas do POST
$host = $_POST['host'] ?? (getenv('DB_HOST') ?: 'localhost');
$port = $_POST['port'] ?? (getenv('DB_PORT') ?: '3306');
$user = $_POST['user'] ?? (getenv('DB_USER') ?: 'root');
$pass = isset($_POST['pass']) ? $_POST['pass'] : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
$dbname = $_POST['dbname'] ?? (getenv('DB_NAME') ?: 'listo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conexão sem especificar banco para poder criá-lo
        $dsnWithoutDb = "mysql:host={$host};port={$port};charset=utf8mb4";
        $pdoRoot = new PDO($dsnWithoutDb, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Criar o banco se não existir
        $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $details[] = "Banco de dados '{$dbname}' verificado/criado com sucesso.";

        // Conectar agora diretamente ao banco
        $dsnWithDb = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsnWithDb, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Ler arquivo database.sql
        $sqlFile = __DIR__ . '/database.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Arquivo database.sql não encontrado na raiz!");
        }

        $sql = file_get_contents($sqlFile);

        // Remover linhas com 'CREATE DATABASE' e 'USE' para não quebrar em hospedagens com prefixo fixo
        $lines = explode("\n", $sql);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (stripos($trimmed, 'CREATE DATABASE') === 0 || stripos($trimmed, 'USE `') === 0) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        // Executar os comandos
        $pdo->exec($cleanSql);
        $details[] = "Tabelas e dados iniciais criados com sucesso!";

        $status = 'success';
        $message = "Instalação concluída com sucesso! Todas as tabelas e dados demonstrativos foram criados.";

    } catch (Exception $e) {
        $status = 'error';
        $message = "Falha durante a instalação: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listo - Instalador de Banco de Dados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen py-10 px-4">
    <div class="max-w-xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        <!-- Topo Decorativo -->
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 p-8 text-white text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur rounded-2xl mb-3 shadow-inner">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight">Listo</h1>
            <p class="text-indigo-100 text-sm mt-1">Assistente de Instalação e Banco de Dados</p>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            <?php if ($status === 'success'): ?>
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-emerald-800">
                    <div class="flex items-center gap-3 font-bold text-lg text-emerald-900 mb-2">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Instalação Concluída!
                    </div>
                    <p class="text-sm mb-4"><?= htmlspecialchars($message); ?></p>
                    
                    <div class="bg-white/80 p-3 rounded-xl border border-emerald-100 text-xs font-mono space-y-1 mb-5">
                        <div class="font-bold text-slate-700">Conta de Demonstração criada:</div>
                        <div>E-mail: <strong class="text-indigo-600">demo@listo.com</strong></div>
                        <div>Senha: <strong class="text-indigo-600">123456</strong></div>
                    </div>

                    <a href="index.php" class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition">
                        Acessar o Listo agora &rarr;
                    </a>
                </div>
            <?php elseif ($status === 'error'): ?>
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-rose-800 text-sm">
                    <div class="font-bold mb-1">Erro na instalação:</div>
                    <p class="font-mono text-xs"><?= htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($status !== 'success'): ?>
                <form method="POST" class="space-y-4">
                    <p class="text-sm text-slate-600">
                        Informe as configurações de acesso ao MySQL para criar o banco de dados e as tabelas necessárias automaticamente:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Host MySQL</label>
                            <input type="text" name="host" value="<?= htmlspecialchars($host); ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <span class="text-[11px] text-slate-400">Geralmente <code>localhost</code></span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Porta</label>
                            <input type="text" name="port" value="<?= htmlspecialchars($port); ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <span class="text-[11px] text-slate-400">Padrão <code>3306</code></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Usuário</label>
                            <input type="text" name="user" value="<?= htmlspecialchars($user); ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <span class="text-[11px] text-slate-400">Padrão <code>root</code> no XAMPP</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Senha</label>
                            <input type="password" name="pass" value="<?= htmlspecialchars($pass); ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Vazio no XAMPP padrão">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nome do Banco de Dados</label>
                        <input type="text" name="dbname" value="<?= htmlspecialchars($dbname); ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <span class="text-[11px] text-slate-400">Será criado se não existir: <code>listo</code></span>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Executar Instalação e Criar Tabelas
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="border-t border-slate-200 pt-4 text-xs text-slate-500 space-y-2">
                <p><strong>Nota para InfinityFree / Wuaze:</strong> Você também pode importar o arquivo <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">database.sql</code> diretamente na aba "Importar" do phpMyAdmin no seu painel.</p>
                <div class="flex justify-between items-center text-slate-400 pt-2">
                    <span>Listo v1.0 &copy; 2026</span>
                    <a href="index.php" class="text-indigo-600 hover:underline">Ir para Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
