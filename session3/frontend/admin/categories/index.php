<?php
/**
 * Liste des Catégories de Services — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

$pageTitle  = 'Gestion des Catégories';
$activePage = 'categories';
$breadcrumb = [['label' => 'Catégories']];

require_once __DIR__ . '/../../../backend/services/CategorieService.php';
require_once __DIR__ . '/../../../backend/services/ClassificationService.php';
require_once __DIR__ . '/../../includes/header.php';

$categorieService = new CategorieService();

// Session pour messages flash
$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Recherche
$search = trim($_GET['search'] ?? '');
try {
    $categories = $categorieService->getAll($search);
} catch (Throwable $e) {
    error_log("Error loading categories: " . $e->getMessage());
    $categories = [];
}

$macroDomains = ClassificationService::getMacroDomains();
?>

<!-- Flash message -->
<?php if ($flashMsg): ?>
<div class="mb-6 p-4 rounded-xl flex items-center justify-between border <?= $flashType === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-red-500/10 border-red-500/30 text-red-300' ?>" data-alert>
    <div class="flex items-center gap-2 text-sm font-medium">
        <span><?= $flashType === 'success' ? '✅' : '❌' ?></span>
        <span><?= htmlspecialchars($flashMsg) ?></span>
    </div>
    <button type="button" class="text-slate-400 hover:text-white transition-colors" data-alert-close>✕</button>
</div>
<?php endif; ?>

<!-- Header de la page -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-extrabold text-white flex items-center gap-2">
            <span>🗂️</span> Catégories de Services
        </h1>
        <p class="text-xs text-slate-400 mt-1">
            <?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?> enregistrée<?= count($categories) > 1 ? 's' : '' ?> (Classifiées via JSON & Architecture OOP)
        </p>
    </div>
    <a href="<?= BASE_URL ?>/frontend/admin/categories/ajouter.php" id="btn-ajouter-categorie"
       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all">
        <span>＋</span> Nouvelle Catégorie
    </a>
</div>

<!-- Widgets Macro-Domaines (Classification JSON) -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl p-5 mb-6 shadow-xl">
    <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-400 mb-3 flex items-center gap-1.5">
        <span>📄</span> Classification JSON — Macro-Domaines d'activité
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <?php foreach ($macroDomains as $domain): ?>
        <div class="bg-[#222240] p-3.5 rounded-xl border border-white/5">
            <div class="text-sm font-bold text-white flex items-center gap-2">
                <span><?= $domain['icon'] ?? '📁' ?></span>
                <span class="truncate"><?= htmlspecialchars($domain['nom'] ?? '') ?></span>
            </div>
            <div class="text-xs text-slate-400 mt-1 line-clamp-2">
                <?= htmlspecialchars($domain['description'] ?? '') ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Barre de recherche -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl p-4 mb-6 shadow-xl">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par nom de catégorie, description..."
                   value="<?= htmlspecialchars($search) ?>"
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors">
            Rechercher
        </button>
        <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/frontend/admin/categories/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors text-center">
            ✕ Effacer
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des catégories -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl shadow-xl overflow-hidden">
    <?php if (empty($categories)): ?>
    <div class="py-16 text-center text-slate-400 px-4">
        <div class="text-5xl mb-3">🗂️</div>
        <h3 class="text-base font-bold text-white mb-1">Aucune catégorie trouvée</h3>
        <p class="text-sm text-slate-400 max-w-md mx-auto mb-6">
            <?= $search
                ? 'Aucun résultat pour « ' . htmlspecialchars($search) . ' ».'
                : 'Commencez par ajouter votre première catégorie de service.' ?>
        </p>
        <?php if (!$search): ?>
        <a href="<?= BASE_URL ?>/frontend/admin/categories/ajouter.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors">
            ＋ Ajouter une Catégorie
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#1c1c34] text-xs text-slate-400 uppercase tracking-wider border-b border-white/5">
                <tr>
                    <th class="py-3.5 px-4 font-semibold">#</th>
                    <th class="py-3.5 px-4 font-semibold">Nom de la catégorie</th>
                    <th class="py-3.5 px-4 font-semibold">Domaine (JSON)</th>
                    <th class="py-3.5 px-4 font-semibold">Description</th>
                    <th class="py-3.5 px-4 font-semibold">Services associés</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($categories as $c): ?>
                <?php
                    $idCat = is_object($c) ? $c->getIdCategorie() : $c['id_categorie'];
                    $nom = is_object($c) ? $c->getNom() : $c['nom'];
                    $desc = is_object($c) ? $c->getDescription() : ($c['description'] ?? '');
                    $nbSrv = is_object($c) ? $c->getNbServices() : ($c['nb_services'] ?? 0);
                    $classification = is_object($c) ? $c->getClassification() : ($c['classification'] ?? []);
                ?>
                <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="py-3.5 px-4 text-xs text-slate-500 font-mono">#<?= $idCat ?></td>
                    <td class="py-3.5 px-4 font-bold text-white"><?= htmlspecialchars($nom) ?></td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                            <?= $classification['icon'] ?? '📁' ?> <?= htmlspecialchars($classification['nom'] ?? 'Général') ?>
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-400 max-w-xs truncate">
                        <?= !empty($desc) ? htmlspecialchars($desc) : '<span class="text-slate-600">— Aucun résumé —</span>' ?>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                            🛠️ <?= $nbSrv ?> service<?= $nbSrv > 1 ? 's' : '' ?>
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/frontend/admin/categories/modifier.php?id=<?= $idCat ?>"
                               class="px-2.5 py-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 text-xs font-medium transition-colors"
                               title="Modifier">
                                ✏️ Modifier
                            </a>
                            <button type="button"
                                    class="px-2.5 py-1.5 rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 text-xs font-medium transition-colors"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/frontend/admin/categories/supprimer.php?id=<?= $idCat ?>',
                                        '<?= htmlspecialchars(addslashes($nom)) ?>',
                                        'la catégorie'
                                    )">
                                🗑️ Supprimer
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
