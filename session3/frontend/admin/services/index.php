<?php
/**
 * Liste des Services (Gigs) — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

$pageTitle  = 'Gestion des Services';
$activePage = 'services';
$breadcrumb = [['label' => 'Services']];

require_once __DIR__ . '/../../../backend/services/ServiceService.php';
require_once __DIR__ . '/../../includes/header.php';

$serviceService = new ServiceService();

$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Recherche
$search = trim($_GET['search'] ?? '');
try {
    $services = $serviceService->getAll($search);
} catch (Throwable $e) {
    error_log("Error loading services: " . $e->getMessage());
    $services = [];
}
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
            <span>🛠️</span> Services & Gigs
        </h1>
        <p class="text-xs text-slate-400 mt-1">
            <?= count($services) ?> service<?= count($services) > 1 ? 's' : '' ?> enregistré<?= count($services) > 1 ? 's' : '' ?> (Classification tarifaire & domaines via JSON)
        </p>
    </div>
    <a href="<?= BASE_URL ?>/frontend/admin/services/ajouter.php" id="btn-ajouter-service"
       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all">
        <span>＋</span> Nouveau Service
    </a>
</div>

<!-- Barre de recherche -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl p-4 mb-6 shadow-xl">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par titre, freelance, catégorie..."
                   value="<?= htmlspecialchars($search) ?>"
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors">
            Rechercher
        </button>
        <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/frontend/admin/services/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors text-center">
            ✕ Effacer
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des services -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl shadow-xl overflow-hidden">
    <?php if (empty($services)): ?>
    <div class="py-16 text-center text-slate-400 px-4">
        <div class="text-5xl mb-3">🛠️</div>
        <h3 class="text-base font-bold text-white mb-1">Aucun service trouvé</h3>
        <p class="text-sm text-slate-400 max-w-md mx-auto mb-6">
            <?= $search
                ? 'Aucun résultat pour « ' . htmlspecialchars($search) . ' ».'
                : 'Commencez par ajouter votre premier service (Gig).' ?>
        </p>
        <?php if (!$search): ?>
        <a href="<?= BASE_URL ?>/frontend/admin/services/ajouter.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors">
            ＋ Ajouter un Service
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#1c1c34] text-xs text-slate-400 uppercase tracking-wider border-b border-white/5">
                <tr>
                    <th class="py-3.5 px-4 font-semibold">#</th>
                    <th class="py-3.5 px-4 font-semibold">Service</th>
                    <th class="py-3.5 px-4 font-semibold">Freelance</th>
                    <th class="py-3.5 px-4 font-semibold">Catégorie</th>
                    <th class="py-3.5 px-4 font-semibold">Tarif & Gamme</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($services as $s): ?>
                <?php
                    $idS = is_object($s) ? $s->getIdService() : $s['id_service'];
                    $titre = is_object($s) ? $s->getTitre() : $s['titre'];
                    $prix = is_object($s) ? $s->getPrix() : (float)$s['prix'];
                    $img = is_object($s) ? $s->getImageService() : ($s['image_service'] ?? null);

                    $freelanceNom = is_object($s) ? trim(($s->getPrenomFreelance() ?? '') . ' ' . ($s->getNomFreelance() ?? '')) : trim(($s['prenom_freelance'] ?? '') . ' ' . ($s['nom_freelance'] ?? ''));
                    $catNom = is_object($s) ? ($s->getNomCategorie() ?? '—') : ($s['nom_categorie'] ?? '—');
                    $priceTier = is_object($s) ? $s->getPriceTier() : ($s['price_tier'] ?? []);
                ?>
                <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="py-3.5 px-4 text-xs text-slate-500 font-mono">#<?= $idS ?></td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($img) && file_exists(__DIR__ . '/../../assets/uploads/services/' . $img)): ?>
                                <img src="<?= BASE_URL ?>/frontend/assets/uploads/services/<?= htmlspecialchars($img) ?>"
                                     class="w-12 h-9 rounded-lg object-cover border border-white/10" alt="<?= htmlspecialchars($titre) ?>">
                            <?php else: ?>
                                <div class="w-12 h-9 rounded-lg bg-[#222240] border border-white/5 flex items-center justify-center text-slate-500 text-xs font-semibold">
                                    🛠️
                                </div>
                            <?php endif; ?>
                            <div class="font-bold text-white max-w-xs truncate" title="<?= htmlspecialchars($titre) ?>">
                                <?= htmlspecialchars($titre) ?>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-xs font-medium text-slate-300">
                        <?= !empty($freelanceNom) ? htmlspecialchars($freelanceNom) : '<span class="text-slate-600">—</span>' ?>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                            <?= htmlspecialchars($catNom) ?>
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-bold text-indigo-400 text-sm"><?= number_format($prix, 2, ',', ' ') ?> DH</div>
                        <?php if (!empty($priceTier['badge'])): ?>
                        <span class="inline-block mt-0.5 text-[11px] font-semibold text-amber-400">
                            <?= $priceTier['badge'] ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/frontend/admin/services/modifier.php?id=<?= $idS ?>"
                               class="px-2.5 py-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 text-xs font-medium transition-colors"
                               title="Modifier">
                                ✏️ Modifier
                            </a>
                            <button type="button"
                                    class="px-2.5 py-1.5 rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 text-xs font-medium transition-colors"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/frontend/admin/services/supprimer.php?id=<?= $idS ?>',
                                        '<?= htmlspecialchars(addslashes($titre)) ?>',
                                        'le service'
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
