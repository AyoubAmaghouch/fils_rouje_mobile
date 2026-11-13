<?php
/**
 * Header / Sidebar Admin — Architecture OOP & Tailwind CSS
 * Include : require_once __DIR__ . '/../../includes/header.php';
 *
 * Variables attendues avant l'include :
 * @var string $pageTitle    Titre de la page
 * @var string $activePage   dashboard|freelances|categories|services|commandes
 * @var array  $breadcrumb   [['label'=>'...', 'url'=>'...'], ...]
 */

if (!defined('ADMIN_ACCESS')) {
    if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }
}

// Valeurs par défaut
$pageTitle  = $pageTitle  ?? 'Admin Panel';
$activePage = $activePage ?? 'dashboard';
$breadcrumb = $breadcrumb ?? [];

// BASE_URL calculé dynamiquement
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../backend/config/config.php';
}

// Chargement des statistiques via DashboardService (OOP)
require_once __DIR__ . '/../../backend/services/DashboardService.php';
$dashboardService = new DashboardService();
try {
    $stats = $dashboardService->getGlobalStats();
} catch (Throwable $e) {
    $stats = ['freelances' => 0, 'categories' => 0, 'services' => 0, 'commandes' => 0];
}

$B = BASE_URL;
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panneau d'administration — Plateforme Freelance">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle) ?> — FreelanceAdmin</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Play CDN configuré) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#6366f1',
                            dark: '#4f46e5',
                            light: '#a5b4fc',
                        },
                        secondary: '#8b5cf6',
                        accent: '#06b6d4',
                        surface: {
                            body: '#0d0d1a',
                            sidebar: '#111120',
                            card: '#18182e',
                            cardHover: '#1e1e38',
                            input: '#222240',
                            topbar: '#16162a',
                            tableHeader: '#1c1c34',
                            tableRow: '#18182e',
                            tableAlt: '#1b1b31',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
</head>
<body class="bg-[#0d0d1a] text-slate-200 font-sans antialiased min-h-screen flex">

    <!-- Overlay Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden transition-opacity duration-300"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#111120] border-r border-white/5 flex flex-col transition-transform duration-300 -translate-x-full md:translate-x-0">

        <!-- Logo -->
        <a href="<?= $B ?>/frontend/admin/index.php" class="h-16 px-6 flex items-center gap-3 border-b border-white/5 hover:bg-white/[0.02] transition-colors">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white text-lg font-bold shadow-lg shadow-indigo-500/25">⚡</div>
            <div class="text-base font-bold text-white tracking-tight">Freelance<span class="text-indigo-400">Admin</span></div>
        </a>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto">

            <div class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Principal</div>

            <a href="<?= $B ?>/frontend/admin/index.php" id="nav-dashboard"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= $activePage === 'dashboard' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <span class="text-base">📊</span>
                <span>Dashboard</span>
            </a>

            <div class="pt-5 px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Gestion</div>

            <a href="<?= $B ?>/frontend/admin/freelances/index.php" id="nav-freelances"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= $activePage === 'freelances' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <div class="flex items-center gap-3">
                    <span class="text-base">👤</span>
                    <span>Freelances</span>
                </div>
                <?php if ($stats['freelances'] > 0): ?>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $activePage === 'freelances' ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-300' ?>"><?= $stats['freelances'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= $B ?>/frontend/admin/categories/index.php" id="nav-categories"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= $activePage === 'categories' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <div class="flex items-center gap-3">
                    <span class="text-base">🗂️</span>
                    <span>Catégories</span>
                </div>
                <?php if ($stats['categories'] > 0): ?>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $activePage === 'categories' ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-300' ?>"><?= $stats['categories'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= $B ?>/frontend/admin/services/index.php" id="nav-services"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= $activePage === 'services' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <div class="flex items-center gap-3">
                    <span class="text-base">🛠️</span>
                    <span>Services</span>
                </div>
                <?php if ($stats['services'] > 0): ?>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $activePage === 'services' ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-300' ?>"><?= $stats['services'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= $B ?>/frontend/admin/commandes/index.php" id="nav-commandes"
               class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= $activePage === 'commandes' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                <div class="flex items-center gap-3">
                    <span class="text-base">📦</span>
                    <span>Commandes</span>
                </div>
                <?php if ($stats['commandes'] > 0): ?>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $activePage === 'commandes' ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-300' ?>"><?= $stats['commandes'] ?></span>
                <?php endif; ?>
            </a>

        </nav>

        <!-- Footer Sidebar -->
        <div class="p-4 border-t border-white/5 bg-black/10">
            <div class="flex items-center gap-3 px-2 py-1.5">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-cyan-400 flex items-center justify-center text-white font-semibold text-sm">A</div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-white truncate">Administrateur</div>
                    <div class="text-[11px] text-slate-400 truncate">Super Admin</div>
                </div>
            </div>
        </div>

    </aside>

    <!-- ===== MAIN CONTENT WRAPPER ===== -->
    <div class="flex-1 md:pl-64 flex flex-col min-h-screen min-w-0">

        <!-- Topbar -->
        <header class="sticky top-0 z-30 h-16 bg-[#16162a]/90 backdrop-blur border-b border-white/5 px-4 md:px-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="hamburgerBtn" aria-label="Menu" class="md:hidden p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
                    ☰
                </button>
                <div>
                    <h1 class="text-base font-bold text-white"><?= htmlspecialchars($pageTitle) ?></h1>
                    <?php if (!empty($breadcrumb)): ?>
                    <nav class="flex items-center gap-1.5 text-xs text-slate-400" aria-label="Fil d'Ariane">
                        <a href="<?= $B ?>/frontend/admin/index.php" class="hover:text-indigo-400 transition-colors">🏠 Accueil</a>
                        <?php foreach ($breadcrumb as $crumb): ?>
                            <span class="text-slate-600">›</span>
                            <?php if (!empty($crumb['url'])): ?>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>" class="hover:text-indigo-400 transition-colors"><?= htmlspecialchars($crumb['label']) ?></a>
                            <?php else: ?>
                                <span class="text-slate-200"><?= htmlspecialchars($crumb['label']) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button title="Rafraîchir" onclick="showToast('Actualisation des données...', 'info', 1500); setTimeout(() => location.reload(), 300);" class="p-2 text-sm rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors">
                    🔄
                </button>
                <button title="Accueil" onclick="location.href='<?= $B ?>/frontend/admin/index.php'" class="p-2 text-sm rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors">
                    🏠
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto">
