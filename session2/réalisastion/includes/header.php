<?php
/**
 * Header / Sidebar Admin — utilise BASE_URL (défini dans config/config.php)
 * Include : require_once __DIR__ . '/../../includes/header.php';
 *
 * Variables attendues avant l'include :
 * @var string $pageTitle    Titre de la page
 * @var string $activePage   dashboard|freelances|categories|services|commandes
 * @var array  $breadcrumb   [['label'=>'...', 'url'=>'...'], ...]
 */

if (!defined('ADMIN_ACCESS')) {
    define('ADMIN_ACCESS', true);
}

// Valeurs par défaut
$pageTitle  = $pageTitle  ?? 'Admin Panel';
$activePage = $activePage ?? 'dashboard';
$breadcrumb = $breadcrumb ?? [];

// BASE_URL est défini via config/database.php → config/config.php
// On s'assure qu'il est disponible même si database.php n'a pas encore été inclus
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// Statistiques pour les badges de la sidebar
if (!function_exists('getDB')) {
    require_once __DIR__ . '/../config/database.php';
}
$pdo   = getDB();
$stats = ['freelances' => 0, 'categories' => 0, 'services' => 0, 'commandes' => 0];
try {
    $stats['freelances']  = (int)$pdo->query("SELECT COUNT(*) FROM freelance")->fetchColumn();
    $stats['categories']  = (int)$pdo->query("SELECT COUNT(*) FROM categorie_service")->fetchColumn();
    $stats['services']    = (int)$pdo->query("SELECT COUNT(*) FROM service")->fetchColumn();
    $stats['commandes']   = (int)$pdo->query("SELECT COUNT(*) FROM commande")->fetchColumn();
} catch (PDOException $e) {
    // Silencieux si les tables n'existent pas encore
}

$B = BASE_URL; // Raccourci local
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panneau d'administration — Plateforme Freelance">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle) ?> — FreelanceAdmin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Admin CSS (chemin absolu calculé dynamiquement) -->
    <link rel="stylesheet" href="<?= $B ?>/assets/css/admin.css">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">

        <!-- Logo -->
        <a href="<?= $B ?>/admin/index.php" class="sidebar-logo">
            <div class="logo-icon">⚡</div>
            <div class="logo-text">Freelance<span>Admin</span></div>
        </a>

        <!-- Navigation -->
        <nav class="sidebar-nav">

            <span class="nav-label">Principal</span>

            <a href="<?= $B ?>/admin/index.php"
               class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>"
               id="nav-dashboard">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>

            <span class="nav-label">Gestion</span>

            <a href="<?= $B ?>/admin/freelances/index.php"
               class="nav-link <?= $activePage === 'freelances' ? 'active' : '' ?>"
               id="nav-freelances">
                <span class="nav-icon">👤</span>
                <span>Freelances</span>
                <?php if ($stats['freelances'] > 0): ?>
                <span class="nav-badge"><?= $stats['freelances'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= $B ?>/admin/categories/index.php"
               class="nav-link <?= $activePage === 'categories' ? 'active' : '' ?>"
               id="nav-categories">
                <span class="nav-icon">🗂️</span>
                <span>Catégories</span>
                <?php if ($stats['categories'] > 0): ?>
                <span class="nav-badge"><?= $stats['categories'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= $B ?>/admin/services/index.php"
               class="nav-link <?= $activePage === 'services' ? 'active' : '' ?>"
               id="nav-services">
                <span class="nav-icon">🛠️</span>
                <span>Services</span>
                <?php if ($stats['services'] > 0): ?>
                <span class="nav-badge"><?= $stats['services'] ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= $B ?>/admin/commandes/index.php"
               class="nav-link <?= $activePage === 'commandes' ? 'active' : '' ?>"
               id="nav-commandes">
                <span class="nav-icon">📦</span>
                <span>Commandes</span>
                <?php if ($stats['commandes'] > 0): ?>
                <span class="nav-badge"><?= $stats['commandes'] ?></span>
                <?php endif; ?>
            </a>

        </nav>

        <!-- Footer Sidebar -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">A</div>
                <div class="user-info">
                    <div class="user-name">Administrateur</div>
                    <div class="user-role">Super Admin</div>
                </div>
            </div>
        </div>

    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div style="display:flex;align-items:center;">
                <button class="hamburger" id="hamburgerBtn" aria-label="Menu">☰</button>
                <div class="topbar-left">
                    <div class="page-title"><?= htmlspecialchars($pageTitle) ?></div>
                    <?php if (!empty($breadcrumb)): ?>
                    <nav class="breadcrumb" aria-label="Fil d'Ariane">
                        <a href="<?= $B ?>/admin/index.php">🏠 Accueil</a>
                        <?php foreach ($breadcrumb as $crumb): ?>
                            <span>›</span>
                            <?php if (!empty($crumb['url'])): ?>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                            <?php else: ?>
                                <span><?= htmlspecialchars($crumb['label']) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-btn" title="Rafraîchir" onclick="location.reload()">🔄</div>
                <div class="topbar-btn" title="Accueil" onclick="location.href='<?= $B ?>/admin/index.php'">🏠</div>
            </div>
        </header>

        <!-- Contenu de la page -->
        <main class="page-wrapper">
