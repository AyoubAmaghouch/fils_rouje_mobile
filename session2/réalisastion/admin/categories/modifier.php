<?php
/**
 * Modifier une Catégorie — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pdo = getDB();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/categories/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categorie_service WHERE id_categorie = ?");
$stmt->execute([$id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    $_SESSION['flash_msg']  = 'Catégorie introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/categories/index.php');
    exit;
}

$pageTitle  = 'Modifier — ' . htmlspecialchars($categorie['nom']);
$activePage = 'categories';
$breadcrumb = [
    ['label' => 'Catégories', 'url' => BASE_URL . '/admin/categories/index.php'],
    ['label' => 'Modifier']
];

$old = $categorie;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nom']         = trim($_POST['nom'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');

    if (empty($old['nom'])) {
        $errors['nom'] = 'Le nom de la catégorie est obligatoire.';
    } else {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM categorie_service WHERE nom = ? AND id_categorie != ?");
        $chk->execute([$old['nom'], $id]);
        if ($chk->fetchColumn() > 0) {
            $errors['nom'] = 'Une autre catégorie utilise déjà ce nom.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE categorie_service SET nom = :nom, description = :description WHERE id_categorie = :id");
        $stmt->execute([
            ':nom'         => $old['nom'],
            ':description' => $old['description'] ?: null,
            ':id'          => $id
        ]);

        $_SESSION['flash_msg']  = 'La catégorie « ' . $old['nom'] . ' » a été modifiée avec succès !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/admin/categories/index.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- En-tête page -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <h1 style="font-size:20px;font-weight:800;margin-bottom:4px;">✏️ Modifier la Catégorie</h1>
        <p style="color:var(--text-muted);font-size:13px;">Modifiez les informations ci-dessous.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/categories/index.php" class="btn btn-outline">← Retour à la liste</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    ❌ <strong>Veuillez corriger les erreurs suivantes :</strong>
    <ul style="margin:8px 0 0 20px;font-size:13px;">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
    <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
</div>
<?php endif; ?>

<!-- Formulaire -->
<form method="POST" novalidate id="form-modifier-categorie">
<div class="card" style="max-width:700px;">

    <div class="card-header">
        <h2 class="card-title"><span class="card-icon">🗂️</span> Informations de la catégorie</h2>
        <span style="font-size:12px;color:var(--text-muted);">ID #<?= $id ?></span>
    </div>

    <div class="form-group" style="margin-bottom:20px;">
        <label for="nom">Nom de la catégorie <span class="required">*</span></label>
        <input type="text" name="nom" id="nom"
               value="<?= htmlspecialchars($old['nom']) ?>" required>
        <?php if (!empty($errors['nom'])): ?>
        <span class="form-error">⚠ <?= htmlspecialchars($errors['nom']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group" style="margin-bottom:24px;">
        <label for="description">Description (optionnel)</label>
        <textarea name="description" id="description" rows="4"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success" id="btn-submit-modifier-cat">
            ✅ Enregistrer les modifications
        </button>
        <a href="<?= BASE_URL ?>/admin/categories/index.php" class="btn btn-outline">
            ✕ Annuler
        </a>
    </div>

</div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
