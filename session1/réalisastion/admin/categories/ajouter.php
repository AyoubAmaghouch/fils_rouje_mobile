<?php
/**
 * Ajouter une Catégorie de Service — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pageTitle  = 'Ajouter une Catégorie';
$activePage = 'categories';
$breadcrumb = [
    ['label' => 'Catégories', 'url' => BASE_URL . '/admin/categories/index.php'],
    ['label' => 'Ajouter']
];

$pdo    = getDB();
$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nom']         = trim($_POST['nom'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');

    if (empty($old['nom'])) {
        $errors['nom'] = 'Le nom de la catégorie est obligatoire.';
    } else {
        // Vérifier si la catégorie existe déjà
        $chk = $pdo->prepare("SELECT COUNT(*) FROM categorie_service WHERE nom = ?");
        $chk->execute([$old['nom']]);
        if ($chk->fetchColumn() > 0) {
            $errors['nom'] = 'Cette catégorie existe déjà.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO categorie_service (nom, description) VALUES (:nom, :description)");
        $stmt->execute([
            ':nom'         => $old['nom'],
            ':description' => $old['description'] ?: null
        ]);

        $_SESSION['flash_msg']  = 'La catégorie « ' . $old['nom'] . ' » a été ajoutée avec succès !';
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
        <h1 style="font-size:20px;font-weight:800;margin-bottom:4px;">➕ Ajouter une Catégorie</h1>
        <p style="color:var(--text-muted);font-size:13px;">Remplissez les informations ci-dessous.</p>
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
<form method="POST" novalidate id="form-ajouter-categorie">
<div class="card" style="max-width:700px;">

    <div class="card-header">
        <h2 class="card-title"><span class="card-icon">🗂️</span> Informations de la catégorie</h2>
    </div>

    <div class="form-group" style="margin-bottom:20px;">
        <label for="nom">Nom de la catégorie <span class="required">*</span></label>
        <input type="text" name="nom" id="nom"
               placeholder="ex : Développement Web, Design Graphique..."
               value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required>
        <?php if (!empty($errors['nom'])): ?>
        <span class="form-error">⚠ <?= htmlspecialchars($errors['nom']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group" style="margin-bottom:24px;">
        <label for="description">Description (optionnel)</label>
        <textarea name="description" id="description" rows="4"
                  placeholder="Décrivez brièvement le type de services regroupés sous cette catégorie..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="btn-submit-categorie">
            ✅ Enregistrer la Catégorie
        </button>
        <a href="<?= BASE_URL ?>/admin/categories/index.php" class="btn btn-outline">
            ✕ Annuler
        </a>
    </div>

</div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
