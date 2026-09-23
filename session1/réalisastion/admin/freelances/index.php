<?php
/**
 * Liste des Freelances — Admin
 */

define('ADMIN_ACCESS', true);

$pageTitle  = 'Gestion des Freelances';
$activePage = 'freelances';
$breadcrumb = [['label' => 'Freelances']];

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

$pdo = getDB();

// Message flash de session
$flashMsg  = $_SESSION['flash_msg']  ?? null;
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Recherche
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM freelance
        WHERE nom LIKE :s OR prenom LIKE :s OR email LIKE :s OR telephone LIKE :s
        ORDER BY id_freelance DESC
    ");
    $stmt->execute([':s' => "%{$search}%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM freelance ORDER BY id_freelance DESC");
}
$freelances = $stmt->fetchAll();
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
            👤 Freelances
        </h1>
        <p style="color:var(--text-muted);font-size:13px;">
            <?= count($freelances) ?> freelance<?= count($freelances) > 1 ? 's' : '' ?> trouvé<?= count($freelances) > 1 ? 's' : '' ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/admin/freelances/ajouter.php" class="btn btn-primary" id="btn-ajouter-freelance">
        ＋ Nouveau Freelance
    </a>
</div>

<!-- Barre de recherche -->
<div class="card" style="margin-bottom:16px;padding:16px;">
    <form method="GET" class="search-bar">
        <div class="search-input-wrapper" style="flex:1;">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" id="search-input"
                   placeholder="Rechercher par nom, prénom, email, téléphone..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/admin/freelances/index.php" class="btn btn-outline">✕ Effacer</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des freelances -->
<div class="card">
    <?php if (empty($freelances)): ?>
    <div class="empty-state">
        <div class="empty-icon">👤</div>
        <h3>Aucun freelance trouvé</h3>
        <p>
            <?= $search
                ? 'Aucun résultat pour « ' . htmlspecialchars($search) . ' ».'
                : 'Commencez par ajouter votre premier freelance.' ?>
        </p>
        <?php if (!$search): ?>
        <a href="<?= BASE_URL ?>/admin/freelances/ajouter.php" class="btn btn-primary" style="margin-top:16px;">
            ＋ Ajouter un Freelance
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Nom & Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Réseaux sociaux</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($freelances as $f): ?>
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">#<?= $f['id_freelance'] ?></td>

                    <!-- Photo -->
                    <td>
                        <?php
                        $imgPath = __DIR__ . '/../../assets/uploads/freelances/' . $f['image'];
                        if (!empty($f['image']) && file_exists($imgPath)):
                        ?>
                            <img src="<?= BASE_URL ?>/assets/uploads/freelances/<?= htmlspecialchars($f['image']) ?>"
                                 class="table-avatar"
                                 alt="<?= htmlspecialchars($f['prenom']) ?>"
                                 title="<?= htmlspecialchars($f['prenom'] . ' ' . $f['nom']) ?>">
                        <?php else: ?>
                            <div class="table-avatar-placeholder">
                                <?= strtoupper(substr($f['prenom'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Nom & Prénom -->
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($f['prenom'] . ' ' . $f['nom']) ?></div>
                        <?php if (!empty($f['description'])): ?>
                        <div style="font-size:11px;color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($f['description']) ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <!-- Email -->
                    <td>
                        <a href="mailto:<?= htmlspecialchars($f['email']) ?>"
                           style="color:var(--primary-light);font-size:13px;">
                            <?= htmlspecialchars($f['email']) ?>
                        </a>
                    </td>

                    <!-- Téléphone -->
                    <td style="font-size:13px;color:var(--text-secondary);">
                        <?= !empty($f['telephone']) ? htmlspecialchars($f['telephone']) : '<span style="color:var(--text-muted);">—</span>' ?>
                    </td>

                    <!-- Réseaux sociaux -->
                    <td>
                        <div class="social-links">
                            <?php if (!empty($f['facebook'])): ?>
                            <a href="<?= htmlspecialchars($f['facebook']) ?>" target="_blank" class="social-link fb" title="Facebook">f</a>
                            <?php endif; ?>
                            <?php if (!empty($f['instagram'])): ?>
                            <a href="<?= htmlspecialchars($f['instagram']) ?>" target="_blank" class="social-link ig" title="Instagram">📷</a>
                            <?php endif; ?>
                            <?php if (!empty($f['linkedin'])): ?>
                            <a href="<?= htmlspecialchars($f['linkedin']) ?>" target="_blank" class="social-link li" title="LinkedIn">in</a>
                            <?php endif; ?>
                            <?php if (!empty($f['github'])): ?>
                            <a href="<?= htmlspecialchars($f['github']) ?>" target="_blank" class="social-link gh" title="GitHub">⌥</a>
                            <?php endif; ?>
                            <?php if (empty($f['facebook']) && empty($f['instagram']) && empty($f['linkedin']) && empty($f['github'])): ?>
                            <span style="color:var(--text-muted);font-size:12px;">—</span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="actions-cell">
                            <a href="<?= BASE_URL ?>/admin/freelances/modifier.php?id=<?= $f['id_freelance'] ?>"
                               class="btn btn-warning btn-sm"
                               id="btn-modifier-<?= $f['id_freelance'] ?>"
                               title="Modifier">
                                ✏️ Modifier
                            </a>
                            <button class="btn btn-danger btn-sm"
                                    id="btn-supprimer-<?= $f['id_freelance'] ?>"
                                    title="Supprimer"
                                    onclick="confirmerSuppression(
                                        '<?= BASE_URL ?>/admin/freelances/supprimer.php?id=<?= $f['id_freelance'] ?>',
                                        '<?= htmlspecialchars(addslashes($f['prenom'] . ' ' . $f['nom'])) ?>',
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
