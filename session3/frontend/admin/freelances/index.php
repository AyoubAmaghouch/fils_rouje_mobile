<?php
/**
 * Liste des Freelances — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

$pageTitle  = 'Gestion des Freelances';
$activePage = 'freelances';
$breadcrumb = [['label' => 'Freelances']];

require_once __DIR__ . '/../../../backend/services/FreelanceService.php';
require_once __DIR__ . '/../../includes/header.php';

$freelanceService = new FreelanceService();

// Message flash de session
$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Recherche
$search = trim($_GET['search'] ?? '');
try {
    $freelances = $freelanceService->getAll($search);
} catch (Throwable $e) {
    error_log("Error loading freelances: " . $e->getMessage());
    $freelances = [];
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
            <span>👤</span> Freelances
        </h1>
        <p class="text-xs text-slate-400 mt-1">
            <?= count($freelances) ?> freelance<?= count($freelances) > 1 ? 's' : '' ?> trouvé<?= count($freelances) > 1 ? 's' : '' ?> (Évalués selon les règles JSON)
        </p>
    </div>
    <a href="<?= BASE_URL ?>/frontend/admin/freelances/ajouter.php" id="btn-ajouter-freelance"
       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all">
        <span>＋</span> Nouveau Freelance
    </a>
</div>

<!-- Barre de recherche -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl p-4 mb-6 shadow-xl">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par nom, prénom, email, téléphone..."
                   value="<?= htmlspecialchars($search) ?>"
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors">
            Rechercher
        </button>
        <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/frontend/admin/freelances/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors text-center">
            ✕ Effacer
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des freelances -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl shadow-xl overflow-hidden">
    <?php if (empty($freelances)): ?>
    <div class="py-16 text-center text-slate-400 px-4">
        <div class="text-5xl mb-3">👤</div>
        <h3 class="text-base font-bold text-white mb-1">Aucun freelance trouvé</h3>
        <p class="text-sm text-slate-400 max-w-md mx-auto mb-6">
            <?= $search
                ? 'Aucun résultat pour « ' . htmlspecialchars($search) . ' ».'
                : 'Commencez par ajouter votre premier freelance.' ?>
        </p>
        <?php if (!$search): ?>
        <a href="<?= BASE_URL ?>/frontend/admin/freelances/ajouter.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors">
            ＋ Ajouter un Freelance
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#1c1c34] text-xs text-slate-400 uppercase tracking-wider border-b border-white/5">
                <tr>
                    <th class="py-3.5 px-4 font-semibold">#</th>
                    <th class="py-3.5 px-4 font-semibold">Freelance</th>
                    <th class="py-3.5 px-4 font-semibold">Téléphone</th>
                    <th class="py-3.5 px-4 font-semibold">Niveau (JSON)</th>
                    <th class="py-3.5 px-4 font-semibold">Services</th>
                    <th class="py-3.5 px-4 font-semibold">Réseaux</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($freelances as $f): ?>
                <?php
                    $idF = is_object($f) ? $f->getIdFreelance() : $f['id_freelance'];
                    $prenom = is_object($f) ? $f->getPrenom() : $f['prenom'];
                    $nom = is_object($f) ? $f->getNom() : $f['nom'];
                    $email = is_object($f) ? $f->getEmail() : $f['email'];
                    $tel = is_object($f) ? $f->getTelephone() : ($f['telephone'] ?? null);
                    $img = is_object($f) ? $f->getImage() : ($f['image'] ?? null);
                    $nbSrv = is_object($f) ? $f->getNbServices() : ($f['nb_services'] ?? 0);
                    $lvl = is_object($f) ? $f->getLevelMeta() : ($f['level_meta'] ?? []);

                    $fb = is_object($f) ? $f->getFacebook() : ($f['facebook'] ?? null);
                    $insta = is_object($f) ? $f->getInstagram() : ($f['instagram'] ?? null);
                    $li = is_object($f) ? $f->getLinkedin() : ($f['linkedin'] ?? null);
                    $gh = is_object($f) ? $f->getGithub() : ($f['github'] ?? null);
                ?>
                <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="py-3.5 px-4 text-xs text-slate-500 font-mono">#<?= $idF ?></td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($img) && file_exists(__DIR__ . '/../../assets/uploads/freelances/' . $img)): ?>
                                <img src="<?= BASE_URL ?>/frontend/assets/uploads/freelances/<?= htmlspecialchars($img) ?>"
                                     class="w-10 h-10 rounded-full object-cover border border-white/10" alt="<?= htmlspecialchars($prenom) ?>">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-violet-500 flex items-center justify-center text-white font-bold text-sm">
                                    <?= strtoupper(substr($prenom, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="font-bold text-white truncate"><?= htmlspecialchars($prenom . ' ' . $nom) ?></div>
                                <div class="text-xs text-slate-400 truncate"><?= htmlspecialchars($email) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-xs text-slate-300 font-mono">
                        <?= !empty($tel) ? htmlspecialchars($tel) : '<span class="text-slate-600">—</span>' ?>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <?= $lvl['badge'] ?? '🌱 Junior' ?>
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                            🛠️ <?= $nbSrv ?> gig<?= $nbSrv > 1 ? 's' : '' ?>
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-1.5 text-base">
                            <?php if (!empty($fb)): ?><a href="<?= htmlspecialchars($fb) ?>" target="_blank" class="hover:opacity-80 transition-opacity" title="Facebook">📘</a><?php endif; ?>
                            <?php if (!empty($insta)): ?><a href="<?= htmlspecialchars($insta) ?>" target="_blank" class="hover:opacity-80 transition-opacity" title="Instagram">📸</a><?php endif; ?>
                            <?php if (!empty($li)): ?><a href="<?= htmlspecialchars($li) ?>" target="_blank" class="hover:opacity-80 transition-opacity" title="LinkedIn">💼</a><?php endif; ?>
                            <?php if (!empty($gh)): ?><a href="<?= htmlspecialchars($gh) ?>" target="_blank" class="hover:opacity-80 transition-opacity" title="GitHub">🐙</a><?php endif; ?>
                            <?php if (empty($fb) && empty($insta) && empty($li) && empty($gh)): ?>
                                <span class="text-slate-600 text-xs">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/frontend/admin/freelances/modifier.php?id=<?= $idF ?>"
                               class="px-2.5 py-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 text-xs font-medium transition-colors"
                               title="Modifier">
                                ✏️ Modifier
                            </a>
                            <button type="button"
                                    class="px-2.5 py-1.5 rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 text-xs font-medium transition-colors"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/frontend/admin/freelances/supprimer.php?id=<?= $idF ?>',
                                        '<?= htmlspecialchars(addslashes($prenom . ' ' . $nom)) ?>',
                                        'le freelance'
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
