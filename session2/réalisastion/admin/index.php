<?php
/**
 * Dashboard — Panneau d'Administration Freelance
 * Affiche les statistiques globales et les dernières activités
 */

define('ADMIN_ACCESS', true);

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDB();

// --- Statistiques ---
$nbFreelances  = $pdo->query("SELECT COUNT(*) FROM freelance")->fetchColumn();
$nbCategories  = $pdo->query("SELECT COUNT(*) FROM categorie_service")->fetchColumn();
$nbServices    = $pdo->query("SELECT COUNT(*) FROM service")->fetchColumn();
$nbCommandes   = $pdo->query("SELECT COUNT(*) FROM commande")->fetchColumn();

// Chiffre d'affaires total
$caTotal = $pdo->query("SELECT COALESCE(SUM(prix_total), 0) FROM commande")->fetchColumn();

// Commandes par statut
$stmtStatuts = $pdo->query("SELECT statut, COUNT(*) as total FROM commande GROUP BY statut ORDER BY total DESC");
$statutsCommandes = $stmtStatuts->fetchAll();

// Derniers freelances ajoutés
$stmtFreelances = $pdo->query("SELECT * FROM freelance ORDER BY id_freelance DESC LIMIT 5");
$derniersFreelances = $stmtFreelances->fetchAll();

// Dernières commandes
$stmtCommandes = $pdo->query("
    SELECT c.*, s.titre AS titre_service
    FROM commande c
    LEFT JOIN service s ON c.id_service = s.id_service
    ORDER BY c.id_commande DESC
    LIMIT 5
");
$derniersCommandes = $stmtCommandes->fetchAll();

// Services les plus commandés
$stmtTopServices = $pdo->query("
    SELECT s.titre, COUNT(c.id_commande) as nb_commandes, SUM(c.prix_total) as revenu
    FROM service s
    LEFT JOIN commande c ON s.id_service = c.id_service
    GROUP BY s.id_service, s.titre
    ORDER BY nb_commandes DESC
    LIMIT 5
");
$topServices = $stmtTopServices->fetchAll();

// Fonction statut badge
function statutBadge(string $statut): string {
    $map = [
        'en attente'  => 'badge-warning',
        'confirmée'   => 'badge-info',
        'en cours'    => 'badge-info',
        'terminée'    => 'badge-success',
        'annulée'     => 'badge-danger',
    ];
    $class = $map[strtolower($statut)] ?? 'badge-secondary';
    return "<span class=\"badge {$class}\">" . htmlspecialchars($statut) . "</span>";
}
?>
<!-- ===== DASHBOARD ===== -->

<!-- Stats Cards -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-info">
            <div class="stat-value"><?= $nbFreelances ?></div>
            <div class="stat-label">Freelances</div>
            <a href="<?= BASE_URL ?>/admin/freelances/index.php" class="stat-link">Voir tous →</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🛠️</div>
        <div class="stat-info">
            <div class="stat-value"><?= $nbServices ?></div>
            <div class="stat-label">Services (Gigs)</div>
            <a href="<?= BASE_URL ?>/admin/services/index.php" class="stat-link">Voir tous →</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <div class="stat-value"><?= $nbCommandes ?></div>
            <div class="stat-label">Commandes</div>
            <a href="<?= BASE_URL ?>/admin/commandes/index.php" class="stat-link">Voir toutes →</a>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format((float)$caTotal, 0, ',', ' ') ?> <small style="font-size:14px;font-weight:500">DH</small></div>
            <div class="stat-label">Chiffre d'affaires</div>
            <a href="<?= BASE_URL ?>/admin/commandes/index.php" class="stat-link">Détails →</a>
        </div>
    </div>

</div>

<!-- Ligne 2 : Derniers freelances + Commandes par statut -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    <!-- Derniers freelances -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><span class="card-icon">👤</span> Derniers Freelances</h2>
            <a href="<?= BASE_URL ?>/admin/freelances/ajouter.php" class="btn btn-primary btn-sm">+ Ajouter</a>
        </div>

        <?php if (empty($derniersFreelances)): ?>
        <div class="empty-state" style="padding:30px">
            <div class="empty-icon" style="font-size:36px">👤</div>
            <p>Aucun freelance enregistré.</p>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($derniersFreelances as $f): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px;background:var(--bg-input);border-radius:8px;">
                <?php if (!empty($f['image']) && file_exists(__DIR__ . '/../assets/uploads/freelances/' . $f['image'])): ?>
                    <img src="<?= BASE_URL ?>/assets/uploads/freelances/<?= htmlspecialchars($f['image']) ?>"
                         class="table-avatar" alt="<?= htmlspecialchars($f['prenom']) ?>">
                <?php else: ?>
                    <div class="table-avatar-placeholder">
                        <?= strtoupper(substr($f['prenom'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($f['prenom'] . ' ' . $f['nom']) ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        <?= htmlspecialchars($f['email']) ?>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/admin/freelances/modifier.php?id=<?= $f['id_freelance'] ?>"
                   class="btn btn-outline btn-sm" style="padding:4px 10px;font-size:11px;">✏️</a>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:14px;text-align:center;">
            <a href="<?= BASE_URL ?>/admin/freelances/index.php" class="btn btn-outline btn-sm">Voir tous les freelances →</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Commandes par statut -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><span class="card-icon">📊</span> Commandes par statut</h2>
            <span style="font-size:12px;color:var(--text-muted);"><?= $nbCommandes ?> au total</span>
        </div>

        <?php if (empty($statutsCommandes)): ?>
        <div class="empty-state" style="padding:30px">
            <div class="empty-icon" style="font-size:36px">📦</div>
            <p>Aucune commande enregistrée.</p>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($statutsCommandes as $s): ?>
            <?php
                $pct = $nbCommandes > 0 ? round(($s['total'] / $nbCommandes) * 100) : 0;
                $colors = ['en attente'=>'var(--warning)','confirmée'=>'var(--info)','en cours'=>'var(--accent)','terminée'=>'var(--success)','annulée'=>'var(--danger)'];
                $color = $colors[strtolower($s['statut'])] ?? 'var(--primary)';
            ?>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px;">
                    <span style="font-weight:600;color:var(--text-primary);"><?= htmlspecialchars(ucfirst($s['statut'])) ?></span>
                    <span style="color:var(--text-muted);"><?= $s['total'] ?> (<?= $pct ?>%)</span>
                </div>
                <div style="background:var(--bg-input);border-radius:4px;height:8px;overflow:hidden;">
                    <div style="width:<?= $pct ?>%;height:100%;background:<?= $color ?>;border-radius:4px;transition:width 1s ease;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);text-align:center;">
            <a href="<?= BASE_URL ?>/admin/commandes/index.php" class="btn btn-outline btn-sm">Gérer les commandes →</a>
        </div>
    </div>

</div>

<!-- Ligne 3 : Dernières commandes + Top services -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    <!-- Dernières commandes -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><span class="card-icon">📦</span> Dernières Commandes</h2>
        </div>

        <?php if (empty($derniersCommandes)): ?>
        <div class="empty-state" style="padding:30px">
            <p>Aucune commande pour le moment.</p>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Service</th>
                        <th>Statut</th>
                        <th>Prix</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($derniersCommandes as $c): ?>
                    <tr>
                        <td style="color:var(--text-muted);font-size:12px;">#<?= $c['id_commande'] ?></td>
                        <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($c['titre_service'] ?? '—') ?>
                        </td>
                        <td><?= statutBadge($c['statut']) ?></td>
                        <td class="price-tag"><?= number_format((float)$c['prix_total'], 2, ',', ' ') ?> DH</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Services -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><span class="card-icon">🏆</span> Top Services</h2>
        </div>

        <?php if (empty($topServices)): ?>
        <div class="empty-state" style="padding:30px">
            <p>Aucun service enregistré.</p>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($topServices as $i => $ts): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px;background:var(--bg-input);border-radius:8px;">
                <span style="font-size:20px;"><?= ['🥇','🥈','🥉','4️⃣','5️⃣'][$i] ?? ($i+1) ?></span>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($ts['titre']) ?>
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        <?= $ts['nb_commandes'] ?> commande<?= $ts['nb_commandes'] > 1 ? 's' : '' ?>
                    </div>
                </div>
                <span class="price-tag" style="font-size:12px;">
                    <?= number_format((float)$ts['revenu'], 0, ',', ' ') ?> DH
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Raccourcis rapides -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><span class="card-icon">⚡</span> Actions rapides</h2>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:12px;">
        <a href="<?= BASE_URL ?>/admin/freelances/ajouter.php" class="btn btn-primary">
            👤 Nouveau Freelance
        </a>
        <a href="<?= BASE_URL ?>/admin/categories/ajouter.php" class="btn btn-outline">
            🗂️ Nouvelle Catégorie
        </a>
        <a href="<?= BASE_URL ?>/admin/services/ajouter.php" class="btn btn-outline">
            🛠️ Nouveau Service
        </a>
        <a href="<?= BASE_URL ?>/admin/commandes/index.php" class="btn btn-outline">
            📦 Voir les Commandes
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
