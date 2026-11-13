<?php
/**
 * Liste des Commandes — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

$pageTitle  = 'Gestion des Commandes';
$activePage = 'commandes';
$breadcrumb = [['label' => 'Commandes']];

require_once __DIR__ . '/../../../backend/services/CommandeService.php';
require_once __DIR__ . '/../../../backend/services/ClassificationService.php';
require_once __DIR__ . '/../../includes/header.php';

$commandeService = new CommandeService();

$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Filtrage par statut ou recherche
$statutFilter = trim($_GET['statut'] ?? '');
$search       = trim($_GET['search'] ?? '');

try {
    $commandes = $commandeService->getAll($search, $statutFilter);
} catch (Throwable $e) {
    error_log("Error loading commandes: " . $e->getMessage());
    $commandes = [];
}

function getStatutBadge(string $statut, array $meta = []): string {
    $colors = [
        'en attente'  => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'confirmée'   => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'en cours'    => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
        'terminée'    => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'annulée'     => 'bg-red-500/10 text-red-400 border-red-500/20',
    ];
    $cls = $colors[strtolower(trim($statut))] ?? 'bg-slate-700/50 text-slate-300 border-slate-600';
    $icon = $meta['icon'] ?? '📌';
    $label = $meta['label'] ?? ucfirst($statut);
    return "<span class=\"inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {$cls}\">{$icon} " . htmlspecialchars($label) . "</span>";
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
            <span>📦</span> Commandes Client
        </h1>
        <p class="text-xs text-slate-400 mt-1">
            <?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?> au total (Workflow & transitions configurés via JSON)
        </p>
    </div>
</div>

<!-- Filtres & Recherche -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl p-4 mb-6 shadow-xl">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par service, freelance, #ID..."
                   value="<?= htmlspecialchars($search) ?>"
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        <div class="sm:w-56">
            <select name="statut" onchange="this.form.submit()"
                    class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                <option value="">-- Tous les statuts --</option>
                <option value="en attente" <?= $statutFilter === 'en attente' ? 'selected' : '' ?>>⏳ En attente</option>
                <option value="confirmée"  <?= $statutFilter === 'confirmée'  ? 'selected' : '' ?>>☑️ Confirmée</option>
                <option value="en cours"   <?= $statutFilter === 'en cours'   ? 'selected' : '' ?>>⚙️ En cours</option>
                <option value="terminée"   <?= $statutFilter === 'terminée'   ? 'selected' : '' ?>>✅ Terminée</option>
                <option value="annulée"    <?= $statutFilter === 'annulée'    ? 'selected' : '' ?>>❌ Annulée</option>
            </select>
        </div>

        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition-colors">
            Filtrer
        </button>

        <?php if ($search || $statutFilter): ?>
        <a href="<?= BASE_URL ?>/frontend/admin/commandes/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors text-center">
            ✕ Effacer
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des commandes -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl shadow-xl overflow-hidden">
    <?php if (empty($commandes)): ?>
    <div class="py-16 text-center text-slate-400 px-4">
        <div class="text-5xl mb-3">📦</div>
        <h3 class="text-base font-bold text-white mb-1">Aucune commande trouvée</h3>
        <p class="text-sm text-slate-400 max-w-md mx-auto">
            <?= ($search || $statutFilter)
                ? 'Aucun résultat correspondant aux critères de recherche.'
                : 'Les commandes passées par les clients apparaîtront ici.' ?>
        </p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#1c1c34] text-xs text-slate-400 uppercase tracking-wider border-b border-white/5">
                <tr>
                    <th class="py-3.5 px-4 font-semibold">#ID</th>
                    <th class="py-3.5 px-4 font-semibold">Date</th>
                    <th class="py-3.5 px-4 font-semibold">Prestation / Service</th>
                    <th class="py-3.5 px-4 font-semibold">Freelance</th>
                    <th class="py-3.5 px-4 font-semibold">Statut (JSON)</th>
                    <th class="py-3.5 px-4 font-semibold">Montant</th>
                    <th class="py-3.5 px-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($commandes as $c): ?>
                <?php
                    $idCmd = is_object($c) ? $c->getIdCommande() : $c['id_commande'];
                    $dateCmd = is_object($c) ? $c->getDateCommande() : $c['date_commande'];
                    $titre = is_object($c) ? ($c->getTitreService() ?? '—') : ($c['titre_service'] ?? '—');
                    $freelanceNom = is_object($c) ? trim(($c->getPrenomFreelance() ?? '') . ' ' . ($c->getNomFreelance() ?? '')) : trim(($c['prenom_freelance'] ?? '') . ' ' . ($c['nom_freelance'] ?? ''));
                    $statut = is_object($c) ? $c->getStatut() : $c['statut'];
                    $prix = is_object($c) ? $c->getPrixTotal() : (float)$c['prix_total'];
                    $meta = is_object($c) ? $c->getStatusMeta() : ($c['status_meta'] ?? []);
                ?>
                <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="py-3.5 px-4 text-xs text-slate-500 font-mono font-bold">#<?= $idCmd ?></td>
                    <td class="py-3.5 px-4 text-xs text-slate-400 font-mono">
                        <?= date('d/m/Y', strtotime($dateCmd)) ?>
                    </td>
                    <td class="py-3.5 px-4 font-bold text-white max-w-xs truncate" title="<?= htmlspecialchars($titre) ?>">
                        <?= htmlspecialchars($titre) ?>
                    </td>
                    <td class="py-3.5 px-4 text-xs font-medium text-slate-300">
                        <?= !empty($freelanceNom) ? htmlspecialchars($freelanceNom) : '<span class="text-slate-600">—</span>' ?>
                    </td>
                    <td class="py-3.5 px-4">
                        <?= getStatutBadge($statut, $meta) ?>
                    </td>
                    <td class="py-3.5 px-4 font-bold text-indigo-400 text-sm">
                        <?= number_format($prix, 2, ',', ' ') ?> DH
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/frontend/admin/commandes/modifier.php?id=<?= $idCmd ?>"
                               class="px-2.5 py-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 text-xs font-medium transition-colors"
                               title="Modifier">
                                ✏️ Statut
                            </a>
                            <button type="button"
                                    class="px-2.5 py-1.5 rounded-lg border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 text-xs font-medium transition-colors"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/frontend/admin/commandes/supprimer.php?id=<?= $idCmd ?>',
                                        'la commande #<?= $idCmd ?>',
                                        'la commande'
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
