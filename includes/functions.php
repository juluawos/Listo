<?php
/**
 * Listo - Funções Auxiliares Globais
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Define uma mensagem flash para ser exibida via TOAST
 *
 * @param string $type success | error | warning | info
 * @param string $message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'info',
        'message' => $message
    ];
}

/**
 * Retorna e consome a mensagem flash da sessão
 *
 * @return array|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitiza texto simples
 */
function clean($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Retorna resposta em JSON e encerra a execução
 */
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Formata valor numérico para Moeda Brasileira (R$)
 */
function formatCurrency($value) {
    return 'R$ ' . number_format((float)($value ?? 0), 2, ',', '.');
}

/**
 * Formata data no padrão brasileiro
 */
function formatDate($dateString, $withTime = false) {
    if (empty($dateString)) return '';
    $timestamp = strtotime($dateString);
    if (!$timestamp) return '';
    return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $timestamp);
}

/**
 * Retorna as iniciais do nome para o avatar
 */
function getInitials($name) {
    $parts = explode(' ', trim($name ?? 'U'));
    $initials = mb_substr($parts[0], 0, 1);
    if (count($parts) > 1) {
        $initials .= mb_substr(end($parts), 0, 1);
    }
    return mb_strtoupper($initials);
}

/**
 * Metadados visuais das categorias suportadas
 */
function getCategoryMeta($tipo = '') {
    $categories = [
        'supermercado' => [
            'nome' => 'Supermercado',
            'icone' => 'shopping-cart',
            'cor' => 'emerald',
            'bg_light' => 'bg-emerald-50',
            'text_color' => 'text-emerald-700',
            'badge_bg' => 'bg-emerald-100',
            'border_color' => 'border-emerald-200',
            'gradient' => 'from-emerald-500 to-teal-600',
            'descricao' => 'Controle de quantidades, unidades, preços e totais'
        ],
        'filmes' => [
            'nome' => 'Filmes & Séries',
            'icone' => 'film',
            'cor' => 'rose',
            'bg_light' => 'bg-rose-50',
            'text_color' => 'text-rose-700',
            'badge_bg' => 'bg-rose-100',
            'border_color' => 'border-rose-200',
            'gradient' => 'from-rose-500 to-pink-600',
            'descricao' => 'Gênero, plataformas de streaming e avaliações'
        ],
        'receitas' => [
            'nome' => 'Receitas',
            'icone' => 'utensils',
            'cor' => 'amber',
            'bg_light' => 'bg-amber-50',
            'text_color' => 'text-amber-700',
            'badge_bg' => 'bg-amber-100',
            'border_color' => 'border-amber-200',
            'gradient' => 'from-amber-500 to-orange-600',
            'descricao' => 'Ingredientes, tempo, rendimento e modo de preparo'
        ],
        'tarefas' => [
            'nome' => 'Tarefas & Rotina',
            'icone' => 'check-square',
            'cor' => 'indigo',
            'bg_light' => 'bg-indigo-50',
            'text_color' => 'text-indigo-700',
            'badge_bg' => 'bg-indigo-100',
            'border_color' => 'border-indigo-200',
            'gradient' => 'from-indigo-500 to-violet-600',
            'descricao' => 'Checklists diários, prioridades e prazos'
        ],
        'viagens' => [
            'nome' => 'Viagens & Bagagem',
            'icone' => 'plane',
            'cor' => 'sky',
            'bg_light' => 'bg-sky-50',
            'text_color' => 'text-sky-700',
            'badge_bg' => 'bg-sky-100',
            'border_color' => 'border-sky-200',
            'gradient' => 'from-sky-500 to-cyan-600',
            'descricao' => 'Organização de malas, documentos e passeios'
        ],
        'outros' => [
            'nome' => 'Outros',
            'icone' => 'list',
            'cor' => 'slate',
            'bg_light' => 'bg-slate-50',
            'text_color' => 'text-slate-700',
            'badge_bg' => 'bg-slate-100',
            'border_color' => 'border-slate-200',
            'gradient' => 'from-slate-600 to-slate-800',
            'descricao' => 'Lista genérica personalizada'
        ]
    ];

    if ($tipo && isset($categories[$tipo])) {
        return $categories[$tipo];
    }
    return $categories;
}
