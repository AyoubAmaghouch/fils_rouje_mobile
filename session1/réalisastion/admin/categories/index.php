<?php
/**
 * Liste des Catégories de Services — Admin
 */

define('ADMIN_ACCESS', true);

$pageTitle  = 'Gestion des Catégories';
$activePage = 'categories';
$breadcrumb = [['label' => 'Catégories']];

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

$pdo = getDB();

// Session pour messages flash
$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Recherche
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT c.*, COUNT(s.id_service) as nb_services
        FROM categorie_service c
        LEFT JOIN service s ON c.id_categorie = s.id_categorie
        WHERE c.nom LIKE :s OR c.description LIKE :s
        GROUP BY c.id_categorie
        ORDER BY c.id_categorie DESC
    ");
    $stmt->execute([':s' => "%{$search}%"]);
} else {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(s.id_service) as nb_services
        FROM categorie_service c
        LEFT JOIN service s ON c.id_categorie = s.id_categorie
        GROUP BY c.id_categorie
        ORDER BY c.id_categorie DESC
    ");
}
$categories = $stmt->fetchAll();
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
            🗂️ Catégories de Services
        </h1>
        <p style="color:var(--text-muted);font-size:13px;">
            <?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?> enregistrée<?= count($categories) > 1 ? 's' : '' ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/admin/categories/ajouter.php" class="btn btn-primary" id="btn-ajouter-categorie">
        ＋ Nouvelle Catégorie
    </a>
</div>

<!-- Barre de recherche -->
<div class="card" style="margin-bottom:16px;padding:16px;">
    <form method="GET" class="search-bar">
        <div class="search-input-wrapper" style="flex:1;">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par nom de catégorie, description..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/admin/categories/index.php" class="btn btn-outline">✕ Effacer</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des catégories -->
<div class="card">
    <?php if (empty($categories)): ?>
    <div class="empty-state">
        <div class="empty-icon">🗂️</div>
        <h3>Aucune catégorie trouvée</h3>
        <p>
            <?= $search
                ? 'Aucun résultat pour « ' . htmlspecialchars($search) . ' ».'
                : 'Commencez par ajouter votre première catégorie de service.' ?>
        </p>
        <?php if (!$search): ?>
        <a href="<?= BASE_URL ?>/admin/categories/ajouter.php" class="btn btn-primary" style="margin-top:16px;">
            ＋ Ajouter une Catégorie
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom de la catégorie</th>
                    <th>Description</th>
                    <th>Services associés</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">#<?= $c['id_categorie'] ?></td>

                    <!-- Nom -->
                    <td>
                        <div style="font-weight:700;color:var(--text-primary);">
                            <?= htmlspecialchars($c['nom']) ?>
                        </div>
                    </td>

                    <!-- Description -->
                    <td>
                        <div style="font-size:13px;color:var(--text-secondary);max-width:300px;">
                            <?= !empty($c['description']) ? htmlspecialchars($c['description']) : '<span style="color:var(--text-muted);">— Aucun résumé —</span>' ?>
                        </div>
                    </td>

                    <!-- Nb Services -->
                    <td>
                        <span class="badge badge-info" style="font-size:12px;">
                            🛠️ <?= $c['nb_services'] ?> service<?= $c['nb_services'] > 1 ? 's' : '' ?>
                        </span>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="actions-cell">
                            <a href="<?= BASE_URL ?>/admin/categories/modifier.php?id=<?= $c['id_categorie'] ?>"
                               class="btn btn-warning btn-sm"
                               title="Modifier">
                                ✏️ Modifier
                            </a>
                            <button class="btn btn-danger btn-sm"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/admin/categories/supprimer.php?id=<?= $c['id_categorie'] ?>',
                                        '<?= htmlspecialchars(addslashes($c['nom'])) ?>',
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
