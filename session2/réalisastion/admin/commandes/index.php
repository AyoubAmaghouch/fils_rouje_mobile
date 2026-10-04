<?php
/**
 * Liste des Commandes — Admin
 */

define('ADMIN_ACCESS', true);

$pageTitle  = 'Gestion des Commandes';
$activePage = 'commandes';
$breadcrumb = [['label' => 'Commandes']];

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

$pdo = getDB();

$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Filtrage par statut ou recherche
$statutFilter = trim($_GET['statut'] ?? '');
$search       = trim($_GET['search'] ?? '');

$sql = "
    SELECT c.*, s.titre AS titre_service, f.nom AS nom_freelance, f.prenom AS prenom_freelance
    FROM commande c
    LEFT JOIN service s ON c.id_service = s.id_service
    LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
    WHERE 1=1
";
$params = [];

if ($statutFilter !== '') {
    $sql .= " AND c.statut = :statut";
    $params[':statut'] = $statutFilter;
}

if ($search !== '') {
    $sql .= " AND (s.titre LIKE :s OR f.nom LIKE :s OR f.prenom LIKE :s OR c.id_commande LIKE :s)";
    $params[':s'] = "%{$search}%";
}

$sql .= " ORDER BY c.id_commande DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll();

// Helper badge statut
function getStatutBadge(string $statut): string {
    $map = [
        'en attente'  => 'badge-warning',
        'confirmée'   => 'badge-info',
        'en cours'    => 'badge-info',
        'terminée'    => 'badge-success',
        'annulée'     => 'badge-danger',
    ];
    $class = $map[strtolower($statut)] ?? 'badge-secondary';
    return "<span class=\"badge {$class}\">" . htmlspecialchars(ucfirst($statut)) . "</span>";
}
?>

<!-- Flash message -->
<?php if ($flashMsg): ?>
<div class="alert alert-<?= htmlspecialchars($flashType) ?>">
    <?= $flashType === 'success' ? '✅' : '❌' ?>
    <?= htmlspecialchars($flashMsg) ?>
    <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
</div>
<?php endif; ?>

<!-- Header de la page -->
<div class="card-header" style="background:none;border:none;padding:0;margin-bottom:20px;">
    <div>
        <h1 style="font-size:20px;font-weight:800;color:var(--text-primary);margin-bottom:4px;">
            📦 Commandes Client
        </h1>
        <p style="color:var(--text-muted);font-size:13px;">
            <?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?> au total
        </p>
    </div>
</div>

<!-- Filtres & Recherche -->
<div class="card" style="margin-bottom:16px;padding:16px;">
    <form method="GET" class="search-bar" style="display:flex;gap:12px;flex-wrap:wrap;">
        <div class="search-input-wrapper" style="flex:1;min-width:200px;">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par service, freelance, #ID..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>

        <div style="min-width:160px;">
            <select name="statut" onchange="this.form.submit()" style="padding:10px 14px;border-radius:8px;border:1px solid var(--border);background:var(--bg-input);color:var(--text-primary);font-size:13px;width:100%;">
                <option value="">-- Tous les statuts --</option>
                <option value="en attente" <?= $statutFilter === 'en attente' ? 'selected' : '' ?>>⏳ En attente</option>
                <option value="confirmée"  <?= $statutFilter === 'confirmée'  ? 'selected' : '' ?>>☑️ Confirmée</option>
                <option value="en cours"   <?= $statutFilter === 'en cours'   ? 'selected' : '' ?>>⚙️ En cours</option>
                <option value="terminée"   <?= $statutFilter === 'terminée'   ? 'selected' : '' ?>>✅ Terminée</option>
                <option value="annulée"    <?= $statutFilter === 'annulée'    ? 'selected' : '' ?>>❌ Annulée</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Filtrer</button>
        <?php if ($search || $statutFilter): ?>
        <a href="<?= BASE_URL ?>/admin/commandes/index.php" class="btn btn-outline">✕ Effacer</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des commandes -->
<div class="card">
    <?php if (empty($commandes)): ?>
    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>Aucune commande trouvée</h3>
        <p>
            <?= ($search || $statutFilter)
                ? 'Aucun résultat ne correspond à vos critères de recherche.'
                : 'Aucune commande enregistrée pour le moment.' ?>
        </p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th># N°</th>
                    <th>Date commande</th>
                    <th>Service commandé</th>
                    <th>Freelance</th>
                    <th>Prix Total</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $cmd): ?>
                <tr>
                    <td style="font-weight:700;color:var(--text-muted);font-size:13px;">#<?= $cmd['id_commande'] ?></td>

                    <!-- Date -->
                    <td style="font-size:13px;color:var(--text-secondary);">
                        📅 <?= date('d/m/Y', strtotime($cmd['date_commande'])) ?>
                    </td>

                    <!-- Service -->
                    <td>
                        <div style="font-weight:600;color:var(--text-primary);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($cmd['titre_service'] ?? 'Service supprimé') ?>
                        </div>
                    </td>

                    <!-- Freelance -->
                    <td>
                        <?php if (!empty($cmd['prenom_freelance'])): ?>
                        <div style="font-size:13px;color:var(--primary-light);">
                            👤 <?= htmlspecialchars($cmd['prenom_freelance'] . ' ' . $cmd['nom_freelance']) ?>
                        </div>
                        <?php else: ?>
                        <span style="color:var(--text-muted);">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Prix Total -->
                    <td>
                        <span class="price-tag">
                            <?= number_format((float)$cmd['prix_total'], 2, ',', ' ') ?> DH
                        </span>
                    </td>

                    <!-- Statut -->
                    <td>
                        <?= getStatutBadge($cmd['statut']) ?>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="actions-cell">
                            <a href="<?= BASE_URL ?>/admin/commandes/modifier.php?id=<?= $cmd['id_commande'] ?>"
                               class="btn btn-warning btn-sm"
                               title="Changer le statut">
                                ✏️ Modifier Statut
                            </a>
                            <button class="btn btn-danger btn-sm"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/admin/commandes/supprimer.php?id=<?= $cmd['id_commande'] ?>',
                                        'Commande #<?= $cmd['id_commande'] ?>',
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
