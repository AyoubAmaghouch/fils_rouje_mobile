<?php
/**
 * Liste des Services (Gigs) — Admin
 */

define('ADMIN_ACCESS', true);

$pageTitle  = 'Gestion des Services';
$activePage = 'services';
$breadcrumb = [['label' => 'Services']];

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

$pdo = getDB();

$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Recherche
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT s.*, f.nom AS nom_freelance, f.prenom AS prenom_freelance, c.nom AS nom_categorie
        FROM service s
        LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
        LEFT JOIN categorie_service c ON s.id_categorie = c.id_categorie
        WHERE s.titre LIKE :s OR s.description LIKE :s OR f.nom LIKE :s OR f.prenom LIKE :s OR c.nom LIKE :s
        ORDER BY s.id_service DESC
    ");
    $stmt->execute([':s' => "%{$search}%"]);
} else {
    $stmt = $pdo->query("
        SELECT s.*, f.nom AS nom_freelance, f.prenom AS prenom_freelance, c.nom AS nom_categorie
        FROM service s
        LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
        LEFT JOIN categorie_service c ON s.id_categorie = c.id_categorie
        ORDER BY s.id_service DESC
    ");
}
$services = $stmt->fetchAll();
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
            🛠️ Services & Gigs
        </h1>
        <p style="color:var(--text-muted);font-size:13px;">
            <?= count($services) ?> service<?= count($services) > 1 ? 's' : '' ?> enregistré<?= count($services) > 1 ? 's' : '' ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/admin/services/ajouter.php" class="btn btn-primary" id="btn-ajouter-service">
        ＋ Nouveau Service
    </a>
</div>

<!-- Barre de recherche -->
<div class="card" style="margin-bottom:16px;padding:16px;">
    <form method="GET" class="search-bar">
        <div class="search-input-wrapper" style="flex:1;">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par titre, freelance, catégorie..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/admin/services/index.php" class="btn btn-outline">✕ Effacer</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des services -->
<div class="card">
    <?php if (empty($services)): ?>
    <div class="empty-state">
        <div class="empty-icon">🛠️</div>
        <h3>Aucun service trouvé</h3>
        <p>
            <?= $search
                ? 'Aucun résultat pour « ' . htmlspecialchars($search) . ' ».'
                : 'Commencez par ajouter votre premier service (Gig).' ?>
        </p>
        <?php if (!$search): ?>
        <a href="<?= BASE_URL ?>/admin/services/ajouter.php" class="btn btn-primary" style="margin-top:16px;">
            ＋ Ajouter un Service
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Titre du service</th>
                    <th>Catégorie</th>
                    <th>Freelance</th>
                    <th>Prix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">#<?= $s['id_service'] ?></td>

                    <!-- Image -->
                    <td>
                        <?php
                        $imgPath = __DIR__ . '/../../assets/uploads/services/' . $s['image_service'];
                        if (!empty($s['image_service']) && file_exists($imgPath)):
                        ?>
                            <img src="<?= BASE_URL ?>/assets/uploads/services/<?= htmlspecialchars($s['image_service']) ?>"
                                 class="table-avatar" style="border-radius:6px;width:44px;height:44px;object-fit:cover;"
                                 alt="<?= htmlspecialchars($s['titre']) ?>">
                        <?php else: ?>
                            <div class="table-avatar-placeholder" style="border-radius:6px;">
                                🛠️
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Titre -->
                    <td>
                        <div style="font-weight:700;color:var(--text-primary);"><?= htmlspecialchars($s['titre']) ?></div>
                        <?php if (!empty($s['description'])): ?>
                        <div style="font-size:11px;color:var(--text-muted);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($s['description']) ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <!-- Catégorie -->
                    <td>
                        <span class="badge badge-secondary">
                            🗂️ <?= htmlspecialchars($s['nom_categorie'] ?? 'Sans catégorie') ?>
                        </span>
                    </td>

                    <!-- Freelance -->
                    <td>
                        <?php if (!empty($s['prenom_freelance'])): ?>
                        <div style="font-weight:600;font-size:13px;color:var(--primary-light);">
                            👤 <?= htmlspecialchars($s['prenom_freelance'] . ' ' . $s['nom_freelance']) ?>
                        </div>
                        <?php else: ?>
                        <span style="color:var(--text-muted);">Non assigné</span>
                        <?php endif; ?>
                    </td>

                    <!-- Prix -->
                    <td>
                        <span class="price-tag">
                            <?= number_format((float)$s['prix'], 2, ',', ' ') ?> DH
                        </span>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="actions-cell">
                            <a href="<?= BASE_URL ?>/admin/services/modifier.php?id=<?= $s['id_service'] ?>"
                               class="btn btn-warning btn-sm"
                               title="Modifier">
                                ✏️ Modifier
                            </a>
                            <button class="btn btn-danger btn-sm"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/admin/services/supprimer.php?id=<?= $s['id_service'] ?>',
                                        '<?= htmlspecialchars(addslashes($s['titre'])) ?>',
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
