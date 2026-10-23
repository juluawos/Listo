<?php
/**
 * Listo - Header Compartilhado com Navegação Responsiva Mobile-First
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$user = currentUser();
$base = getBasePath();
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($pageTitle) ? clean($pageTitle) . ' - Listo' : 'Listo - Listas Inteligentes & Compartilhadas'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Estilos Customizados -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/custom.css">
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased bg-slate-50">

    <?php if ($user): ?>
    <!-- Top Navbar (Desktop & Mobile) -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <a href="<?= $base ?>views/dashboard.php" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center shadow-md shadow-indigo-200 group-hover:scale-105 transition">
                            <i data-lucide="check-check" class="w-6 h-6 stroke-[2.5]"></i>
                        </div>
                        <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-indigo-700 to-purple-700 bg-clip-text text-transparent">
                            Listo
                        </span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-1">
                    <a href="<?= $base ?>views/dashboard.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentScript === 'dashboard.php' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <div class="flex items-center gap-2">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                            <span>Painel</span>
                        </div>
                    </a>
                    <a href="<?= $base ?>views/lists.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentScript === 'lists.php' || $currentScript === 'list_view.php' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <div class="flex items-center gap-2">
                            <i data-lucide="list-todo" class="w-4 h-4"></i>
                            <span>Listas</span>
                        </div>
                    </a>
                    <a href="<?= $base ?>views/groups.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentScript === 'groups.php' || $currentScript === 'group_view.php' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <div class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span>Grupos & Família</span>
                        </div>
                    </a>
                    <a href="<?= $base ?>views/history.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold transition <?= $currentScript === 'history.php' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <div class="flex items-center gap-2">
                            <i data-lucide="archive" class="w-4 h-4"></i>
                            <span>Histórico & Lixeira</span>
                        </div>
                    </a>
                </nav>

                <!-- User Dropdown & Profile Actions -->
                <div class="flex items-center gap-3">
                    <a href="<?= $base ?>views/profile.php" class="flex items-center gap-2.5 p-1.5 sm:px-3 sm:py-1.5 rounded-xl hover:bg-slate-100 transition">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                            <?= getInitials($user['nome']); ?>
                        </div>
                        <span class="hidden sm:inline-block text-sm font-medium text-slate-700 max-w-[120px] truncate">
                            <?= clean($user['nome']); ?>
                        </span>
                    </a>

                    <form action="<?= $base ?>actions/auth_logout.php" method="POST" class="inline">
                        <button type="submit" data-confirm="Deseja realmente sair da sua conta?" data-confirm-title="Encerrar Sessão" data-confirm-color="amber" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Sair da conta">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>
    <?php endif; ?>

    <!-- Main Content Wrapper (padding-bottom for mobile bottom navigation bar) -->
    <main class="flex-1 <?= $user ? 'pb-24 md:pb-12' : '' ?>">
