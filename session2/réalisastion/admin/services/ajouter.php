<?php
/**
 * Ajouter un Service (Gig) — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pageTitle  = 'Ajouter un Service';
$activePage = 'services';
$breadcrumb = [
    ['label' => 'Services', 'url' => BASE_URL . '/admin/services/index.php'],
    ['label' => 'Ajouter']
];

$pdo    = getDB();
$errors = [];
$old    = [];

// Récupérer freelances et catégories pour les selects
$freelances = $pdo->query("SELECT id_freelance, nom, prenom FROM freelance ORDER BY prenom ASC")->fetchAll();
$categories = $pdo->query("SELECT id_categorie, nom FROM categorie_service ORDER BY nom ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['titre']        = trim($_POST['titre'] ?? '');
    $old['id_freelance'] = filter_input(INPUT_POST, 'id_freelance', FILTER_VALIDATE_INT);
    $old['id_categorie'] = filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT);
    $old['prix']         = filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT);
    $old['description']  = trim($_POST['description'] ?? '');

    // Validation
    if (empty($old['titre']))        $errors['titre']        = 'Le titre est obligatoire.';
    if (empty($old['id_freelance'])) $errors['id_freelance'] = 'Veuillez sélectionner un freelance.';
    if (empty($old['id_categorie'])) $errors['id_categorie'] = 'Veuillez sélectionner une catégorie.';
    if ($old['prix'] === false || $old['prix'] <= 0) $errors['prix'] = 'Le prix doit être un nombre positif.';
    if (empty($old['description']))  $errors['description']  = 'La description est obligatoire.';

    // Gestion de l'image
    $imageName = null;
    if (!empty($_FILES['image_service']['name'])) {
        $file       = $_FILES['image_service'];
        $extension  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMime= ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        $mime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = @mime_content_type($file['tmp_name']);
        } elseif (function_exists('getimagesize')) {
            $imgInfo = @getimagesize($file['tmp_name']);
            $mime    = $imgInfo['mime'] ?? null;
        }
        if (empty($mime)) {
            $mime = $file['type'] ?? '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['image_service'] = 'Erreur lors de l\'upload de l\'image.';
        } elseif (!in_array($extension, $allowedExt)) {
            $errors['image_service'] = 'Extension non autorisée (JPG, PNG, GIF, WEBP).';
        } elseif (!in_array($mime, $allowedMime)) {
            $errors['image_service'] = 'Type MIME non autorisé.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors['image_service'] = 'L\'image ne doit pas dépasser 5 MB.';
        } else {
            $imageName = uniqid('service_', true) . '.' . $extension;
        }
    }

    if (empty($errors)) {
        if ($imageName) {
            $uploadDir = __DIR__ . '/../../assets/uploads/services/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['image_service']['tmp_name'], $uploadDir . $imageName);
        }

        $stmt = $pdo->prepare("
            INSERT INTO service (titre, description, prix, image_service, id_freelance, id_categorie)
            VALUES (:titre, :description, :prix, :image_service, :id_freelance, :id_categorie)
        ");
        $stmt->execute([
            ':titre'         => $old['titre'],
            ':description'   => $old['description'],
            ':prix'          => $old['prix'],
            ':image_service' => $imageName,
            ':id_freelance'  => $old['id_freelance'],
            ':id_categorie'  => $old['id_categorie']
        ]);

        $_SESSION['flash_msg']  = 'Le service « ' . $old['titre'] . ' » a été créé avec succès !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/admin/services/index.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- En-tête page -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <h1 style="font-size:20px;font-weight:800;margin-bottom:4px;">➕ Ajouter un Service</h1>
        <p style="color:var(--text-muted);font-size:13px;">Proposez un nouveau service (Gig).</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/services/index.php" class="btn btn-outline">← Retour à la liste</a>
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
<form method="POST" enctype="multipart/form-data" novalidate id="form-ajouter-service">
<div class="card">

    <div class="card-header">
        <h2 class="card-title"><span class="card-icon">🛠️</span> Informations du service</h2>
    </div>

    <div style="display:flex;gap:24px;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;">

        <!-- Image preview -->
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
            <div class="image-preview-box" id="preview-image"
                 onclick="document.getElementById('image_service').click()"
                 title="Cliquez pour choisir une image">
                <div class="preview-placeholder">
                    <span class="preview-icon">🖼️</span>
                    Image du<br>service
                </div>
            </div>
            <span style="font-size:11px;color:var(--text-muted);">JPG, PNG, WEBP — max 5 MB</span>
            <?php if (!empty($errors['image_service'])): ?>
            <span class="form-error">⚠ <?= htmlspecialchars($errors['image_service']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Champs -->
        <div class="form-grid" style="flex:1;">

            <div class="form-group" style="grid-column:1 / -1;">
                <label for="titre">Titre du service <span class="required">*</span></label>
                <input type="text" name="titre" id="titre"
                       placeholder="ex : Création de site vitrine complet, Logo moderne..."
                       value="<?= htmlspecialchars($old['titre'] ?? '') ?>" required>
                <?php if (!empty($errors['titre'])): ?>
                <span class="form-error">⚠ <?= htmlspecialchars($errors['titre']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="id_freelance">Freelance prestataire <span class="required">*</span></label>
                <select name="id_freelance" id="id_freelance" required>
                    <option value="">-- Choisir un freelance --</option>
                    <?php foreach ($freelances as $f): ?>
                    <option value="<?= $f['id_freelance'] ?>" <?= ($old['id_freelance'] ?? '') == $f['id_freelance'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['prenom'] . ' ' . $f['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['id_freelance'])): ?>
                <span class="form-error">⚠ <?= htmlspecialchars($errors['id_freelance']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="id_categorie">Catégorie <span class="required">*</span></label>
                <select name="id_categorie" id="id_categorie" required>
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id_categorie'] ?>" <?= ($old['id_categorie'] ?? '') == $c['id_categorie'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['id_categorie'])): ?>
                <span class="form-error">⚠ <?= htmlspecialchars($errors['id_categorie']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="prix">Prix (DH) <span class="required">*</span></label>
                <input type="number" name="prix" id="prix" step="0.01" min="0"
                       placeholder="ex : 1500.00"
                       value="<?= htmlspecialchars($old['prix'] ?? '') ?>" required>
                <?php if (!empty($errors['prix'])): ?>
                <span class="form-error">⚠ <?= htmlspecialchars($errors['prix']) ?></span>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <input type="file" name="image_service" id="image_service"
           data-preview="preview-image"
           accept="image/*"
           style="display:none;">

    <div class="form-group" style="margin-bottom:24px;">
        <label for="description">Description détaillée du service <span class="required">*</span></label>
        <textarea name="description" id="description" rows="5"
                  placeholder="Expliquez ce qui est inclus dans cette prestation..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        <?php if (!empty($errors['description'])): ?>
        <span class="form-error">⚠ <?= htmlspecialchars($errors['description']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="btn-submit-service">
            ✅ Enregistrer le Service
        </button>
        <a href="<?= BASE_URL ?>/admin/services/index.php" class="btn btn-outline">
            ✕ Annuler
        </a>
    </div>

</div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
