<?php
/**
 * Dashboard — Panneau d'Administration Freelance (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/../../backend/services/DashboardService.php';
require_once __DIR__ . '/../includes/header.php';

$dashboardService = new DashboardService();

try {
    $stats = $dashboardService->getGlobalStats();
    $statutsCommandes = $dashboardService->getCommandesByStatus();
    $derniersFreelances = $dashboardService->getRecentFreelances(5);
    $derniersCommandes = $dashboardService->getRecentCommandes(5);
    $topServices = $dashboardService->getTopServices(5);
} catch (Throwable $e) {
    error_log("Dashboard load error: " . $e->getMessage());
    $stats = ['freelances' => 0, 'categories' => 0, 'services' => 0, 'commandes' => 0, 'ca_total' => 0];
    $statutsCommandes = [];
    $derniersFreelances = [];
    $derniersCommandes = [];
    $topServices = [];
}

// Fonction statut badge Tailwind
function statutBadge(string $statut): string {
    $map = [
        'en attente'  => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'confirmée'   => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'en cours'    => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
        'terminée'    => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'annulée'     => 'bg-red-500/10 text-red-400 border-red-500/20',
    ];
    $cls = $map[strtolower(trim($statut))] ?? 'bg-slate-700/50 text-slate-300 border-slate-600';
    return "<span class=\"inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {$cls}\">" . htmlspecialchars($statut) . "</span>";
}
?>

<!-- ===== STATS CARDS ===== -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:border-indigo-500/30 transition-all shadow-xl">
        <div class="w-13 h-13 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-2xl p-3">👤</div>
        <div class="min-w-0 flex-1">
            <div class="text-2xl font-extrabold text-white tracking-tight"><?= $stats['freelances'] ?></div>
            <div class="text-xs font-medium text-slate-400 mb-1">Freelances</div>
            <a href="<?= BASE_URL ?>/frontend/admin/freelances/index.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold inline-flex items-center gap-1 transition-colors">
                Voir tous →
            </a>
        </div>
    </div>

    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:border-indigo-500/30 transition-all shadow-xl">
        <div class="w-13 h-13 rounded-2xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-2xl p-3">🛠️</div>
        <div class="min-w-0 flex-1">
            <div class="text-2xl font-extrabold text-white tracking-tight"><?= $stats['services'] ?></div>
            <div class="text-xs font-medium text-slate-400 mb-1">Services (Gigs)</div>
            <a href="<?= BASE_URL ?>/frontend/admin/services/index.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold inline-flex items-center gap-1 transition-colors">
                Voir tous →
            </a>
        </div>
    </div>

    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:border-indigo-500/30 transition-all shadow-xl">
        <div class="w-13 h-13 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-2xl p-3">📦</div>
        <div class="min-w-0 flex-1">
            <div class="text-2xl font-extrabold text-white tracking-tight"><?= $stats['commandes'] ?></div>
            <div class="text-xs font-medium text-slate-400 mb-1">Commandes</div>
            <a href="<?= BASE_URL ?>/frontend/admin/commandes/index.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold inline-flex items-center gap-1 transition-colors">
                Voir toutes →
            </a>
        </div>
    </div>

    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:border-indigo-500/30 transition-all shadow-xl">
        <div class="w-13 h-13 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-2xl p-3">💰</div>
        <div class="min-w-0 flex-1">
            <div class="text-2xl font-extrabold text-white tracking-tight"><?= number_format((float)$stats['ca_total'], 0, ',', ' ') ?> <span class="text-sm font-medium text-slate-400">DH</span></div>
            <div class="text-xs font-medium text-slate-400 mb-1">Chiffre d'affaires</div>
            <a href="<?= BASE_URL ?>/frontend/admin/commandes/index.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold inline-flex items-center gap-1 transition-colors">
                Détails →
            </a>
        </div>
    </div>

</div>

<!-- ===== LIGNE 2 : DERNIERS FREELANCES + COMMANDES PAR STATUT ===== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    <!-- Derniers freelances -->
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/5">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span>👤</span> Derniers Freelances
                </h2>
                <a href="<?= BASE_URL ?>/frontend/admin/freelances/ajouter.php" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 transition-colors">
                    + Ajouter
                </a>
            </div>

            <?php if (empty($derniersFreelances)): ?>
            <div class="py-12 text-center text-slate-400">
                <div class="text-4xl mb-2">👤</div>
                <p class="text-sm">Aucun freelance enregistré.</p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($derniersFreelances as $f): ?>
                <?php
                    $idFreelance = is_object($f) ? $f->getIdFreelance() : $f['id_freelance'];
                    $prenom = is_object($f) ? $f->getPrenom() : $f['prenom'];
                    $nom = is_object($f) ? $f->getNom() : $f['nom'];
                    $email = is_object($f) ? $f->getEmail() : $f['email'];
                    $img = is_object($f) ? $f->getImage() : ($f['image'] ?? null);
                ?>
                <div class="flex items-center gap-3 p-3 bg-[#222240] rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                    <?php if (!empty($img) && file_exists(__DIR__ . '/../assets/uploads/freelances/' . $img)): ?>
                        <img src="<?= BASE_URL ?>/frontend/assets/uploads/freelances/<?= htmlspecialchars($img) ?>"
                             class="w-10 h-10 rounded-full object-cover border border-white/10" alt="<?= htmlspecialchars($prenom) ?>">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-violet-500 flex items-center justify-center text-white font-bold text-sm">
                            <?= strtoupper(substr($prenom, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-white truncate">
                            <?= htmlspecialchars($prenom . ' ' . $nom) ?>
                        </div>
                        <div class="text-xs text-slate-400 truncate">
                            <?= htmlspecialchars($email) ?>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/frontend/admin/freelances/modifier.php?id=<?= $idFreelance ?>"
                       class="px-2.5 py-1.5 rounded-lg border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-xs transition-colors" title="Modifier">
                        ✏️
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($derniersFreelances)): ?>
        <div class="mt-4 pt-4 border-t border-white/5 text-center">
            <a href="<?= BASE_URL ?>/frontend/admin/freelances/index.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                Voir tous les freelances →
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Commandes par statut -->
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/5">
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span>📊</span> Commandes par statut
                </h2>
                <span class="text-xs text-slate-400 font-medium"><?= $stats['commandes'] ?> au total</span>
            </div>

            <?php if (empty($statutsCommandes)): ?>
            <div class="py-12 text-center text-slate-400">
                <div class="text-4xl mb-2">📦</div>
                <p class="text-sm">Aucune commande enregistrée.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($statutsCommandes as $s): ?>
                <?php
                    $totalCmd = $stats['commandes'];
                    $pct = $totalCmd > 0 ? round(($s['total'] / $totalCmd) * 100) : 0;
                    $barColors = [
                        'en attente' => 'bg-amber-500',
                        'confirmée'  => 'bg-blue-500',
                        'en cours'   => 'bg-cyan-500',
                        'terminée'   => 'bg-emerald-500',
                        'annulée'    => 'bg-red-500',
                    ];
                    $barColor = $barColors[strtolower(trim($s['statut']))] ?? 'bg-indigo-500';
                ?>
                <div>
                    <div class="flex justify-between items-center text-xs mb-1.5">
                        <span class="font-semibold text-slate-200"><?= htmlspecialchars(ucfirst($s['statut'])) ?></span>
                        <span class="text-slate-400 font-mono"><?= $s['total'] ?> (<?= $pct ?>%)</span>
                    </div>
                    <div class="bg-[#222240] rounded-full h-2 overflow-hidden">
                        <div class="<?= $barColor ?> h-full rounded-full transition-all duration-700" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-4 pt-4 border-t border-white/5 text-center">
            <a href="<?= BASE_URL ?>/frontend/admin/commandes/index.php" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                Gérer les commandes →
            </a>
        </div>
    </div>

</div>

<!-- ===== LIGNE 3 : DERNIÈRES COMMANDES + TOP SERVICES ===== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

    <!-- Dernières commandes -->
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/5">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>📦</span> Dernières Commandes
            </h2>
        </div>

        <?php if (empty($derniersCommandes)): ?>
        <div class="py-12 text-center text-slate-400">
            <p class="text-sm">Aucune commande pour le moment.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs text-slate-400 border-b border-white/5">
                        <th class="pb-3 font-semibold">#</th>
                        <th class="pb-3 font-semibold">Service</th>
                        <th class="pb-3 font-semibold">Statut</th>
                        <th class="pb-3 font-semibold text-right">Prix</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($derniersCommandes as $c): ?>
                    <?php
                        $idCmd = is_object($c) ? $c->getIdCommande() : $c['id_commande'];
                        $titreService = is_object($c) ? $c->getTitreService() : ($c['titre_service'] ?? '—');
                        $statut = is_object($c) ? $c->getStatut() : $c['statut'];
                        $prix = is_object($c) ? $c->getPrixTotal() : (float)$c['prix_total'];
                    ?>
                    <tr class="hover:bg-white/[0.02] transition-colors">
                        <td class="py-3 text-xs text-slate-500 font-mono">#<?= $idCmd ?></td>
                        <td class="py-3 font-medium text-white max-w-[140px] truncate"><?= htmlspecialchars($titreService) ?></td>
                        <td class="py-3"><?= statutBadge($statut) ?></td>
                        <td class="py-3 text-right font-bold text-indigo-400"><?= number_format($prix, 2, ',', ' ') ?> DH</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Services -->
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/5">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>🏆</span> Top Services
            </h2>
        </div>

        <?php if (empty($topServices)): ?>
        <div class="py-12 text-center text-slate-400">
            <p class="text-sm">Aucun service enregistré.</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($topServices as $i => $ts): ?>
            <div class="flex items-center gap-3 p-3 bg-[#222240] rounded-xl border border-white/5">
                <span class="text-xl"><?= ['🥇','🥈','🥉','4️⃣','5️⃣'][$i] ?? ($i+1) ?></span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($ts['titre']) ?></div>
                    <div class="text-xs text-slate-400"><?= $ts['nb_commandes'] ?> commande<?= $ts['nb_commandes'] > 1 ? 's' : '' ?></div>
                </div>
                <div class="text-sm font-bold text-indigo-400"><?= number_format((float)$ts['revenu'], 0, ',', ' ') ?> DH</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ===== RACCOURCIS RAPIDES ===== -->
<div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl">
    <div class="pb-3 mb-4 border-b border-white/5">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <span>⚡</span> Actions rapides
        </h2>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="<?= BASE_URL ?>/frontend/admin/freelances/ajouter.php"
           class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-md shadow-indigo-600/30 transition-all flex items-center gap-2">
            <span>👤</span> Nouveau Freelance
        </a>
        <a href="<?= BASE_URL ?>/frontend/admin/categories/ajouter.php"
           class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-200 hover:text-white hover:bg-white/5 text-sm font-medium transition-all flex items-center gap-2">
            <span>🗂️</span> Nouvelle Catégorie
        </a>
        <a href="<?= BASE_URL ?>/frontend/admin/services/ajouter.php"
           class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-200 hover:text-white hover:bg-white/5 text-sm font-medium transition-all flex items-center gap-2">
            <span>🛠️</span> Nouveau Service
        </a>
        <a href="<?= BASE_URL ?>/frontend/admin/commandes/index.php"
           class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-200 hover:text-white hover:bg-white/5 text-sm font-medium transition-all flex items-center gap-2">
            <span>📦</span> Voir les Commandes
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
