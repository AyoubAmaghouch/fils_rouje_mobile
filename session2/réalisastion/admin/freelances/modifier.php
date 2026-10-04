<?php
/**
 * Modifier un Freelance — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pdo = getDB();
$errors = [];

// Récupérer l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/freelances/index.php');
    exit;
}

// Récupérer le freelance
$stmt = $pdo->prepare("SELECT * FROM freelance WHERE id_freelance = ?");
$stmt->execute([$id]);
$freelance = $stmt->fetch();

if (!$freelance) {
    $_SESSION['flash_msg']  = 'Freelance introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/freelances/index.php');
    exit;
}

$pageTitle  = 'Modifier — ' . htmlspecialchars($freelance['prenom'] . ' ' . $freelance['nom']);
$activePage = 'freelances';
$breadcrumb = [
    ['label' => 'Freelances', 'url' => BASE_URL . '/admin/freelances/index.php'],
    ['label' => 'Modifier']
];

// Valeurs du formulaire (POST ou DB)
$old = $freelance;

// Récupérer les catégories et services pour la section Compétences et services
$allCategories = $pdo->query("SELECT * FROM categorie_service ORDER BY nom ASC")->fetchAll();
$allServices   = $pdo->query("SELECT s.*, c.nom AS nom_categorie FROM service s LEFT JOIN categorie_service c ON s.id_categorie = c.id_categorie ORDER BY s.titre ASC")->fetchAll();

// Services actuellement rattachés à ce freelance
$assignedServices   = $pdo->query("SELECT id_service, id_categorie FROM service WHERE id_freelance = " . (int)$id)->fetchAll();
$assignedServiceIds  = array_map('intval', array_column($assignedServices, 'id_service'));
$assignedCategoryIds = array_map('intval', array_unique(array_column($assignedServices, 'id_categorie')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCategories = array_map('intval', $_POST['categories'] ?? []);
    $selectedServices   = array_map('intval', $_POST['services']   ?? []);
} else {
    $selectedCategories = $assignedCategoryIds;
    $selectedServices   = $assignedServiceIds;
}

// =========================================================
// TRAITEMENT DU FORMULAIRE (POST)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['nom']         = trim($_POST['nom'] ?? '');
    $old['prenom']      = trim($_POST['prenom'] ?? '');
    $old['email']       = trim($_POST['email'] ?? '');
    $old['telephone']   = trim($_POST['telephone'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');
    $old['facebook']    = trim($_POST['facebook'] ?? '');
    $old['instagram']   = trim($_POST['instagram'] ?? '');
    $old['linkedin']    = trim($_POST['linkedin'] ?? '');
    $old['github']      = trim($_POST['github'] ?? '');

    // Validation
    if (empty($old['nom']))    $errors['nom']    = 'Le nom est obligatoire.';
    if (empty($old['prenom'])) $errors['prenom'] = 'Le prénom est obligatoire.';

    if (empty($old['email'])) {
        $errors['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'L\'adresse email n\'est pas valide.';
    } else {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM freelance WHERE email = ? AND id_freelance != ?");
        $chk->execute([$old['email'], $id]);
        if ($chk->fetchColumn() > 0) {
            $errors['email'] = 'Cette adresse email est déjà utilisée par un autre freelance.';
        }
    }

    foreach (['facebook','instagram','linkedin','github'] as $reseau) {
        if (!empty($old[$reseau]) && !filter_var($old[$reseau], FILTER_VALIDATE_URL)) {
            $errors[$reseau] = 'L\'URL n\'est pas valide.';
        }
    }

    // Gestion de la nouvelle image
    $imageName = $freelance['image']; // Garder l'ancienne par défaut
    $supprimerImage = isset($_POST['supprimer_image']) && $_POST['supprimer_image'] === '1';

    if (!empty($_FILES['image']['name'])) {
        $file      = $_FILES['image'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon'];

        // Détection du type MIME sécurisée et compatible (avec/sans extension fileinfo)
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
            $errors['image'] = 'Erreur lors de l\'upload.';
        } elseif (!in_array($extension, $allowedExt)) {
            $errors['image'] = 'Extension non autorisée (JPG, PNG, GIF, WEBP).';
        } elseif (!in_array($mime, $allowedMime)) {
            $errors['image'] = 'Type MIME non autorisé.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors['image'] = 'L\'image ne doit pas dépasser 5 MB.';
        } else {
            $newImageName = uniqid('freelance_', true) . '.' . $extension;

            // Supprimer l'ancienne image si elle existe
            $uploadDir = __DIR__ . '/../../assets/uploads/freelances/';
            if ($freelance['image'] && file_exists($uploadDir . $freelance['image'])) {
                unlink($uploadDir . $freelance['image']);
            }
            move_uploaded_file($file['tmp_name'], $uploadDir . $newImageName);
            $imageName = $newImageName;
        }
    } elseif ($supprimerImage) {
        $uploadDir = __DIR__ . '/../../assets/uploads/freelances/';
        if ($freelance['image'] && file_exists($uploadDir . $freelance['image'])) {
            unlink($uploadDir . $freelance['image']);
        }
        $imageName = null;
    }

    // Si aucune erreur → mise à jour
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE freelance SET
                nom = :nom, prenom = :prenom, email = :email,
                telephone = :telephone, description = :description,
                image = :image, facebook = :facebook,
                instagram = :instagram, linkedin = :linkedin, github = :github
            WHERE id_freelance = :id
        ");
        $stmt->execute([
            ':nom'         => $old['nom'],
            ':prenom'      => $old['prenom'],
            ':email'       => $old['email'],
            ':telephone'   => $old['telephone'] ?: null,
            ':description' => $old['description'] ?: null,
            ':image'       => $imageName,
            ':facebook'    => $old['facebook'] ?: null,
            ':instagram'   => $old['instagram'] ?: null,
            ':linkedin'    => $old['linkedin'] ?: null,
            ':github'      => $old['github'] ?: null,
            ':id'          => $id,
        ]);

        // Mise à jour des services rattachés au freelance
        if (!empty($assignedServiceIds)) {
            $unselectedServices = array_diff($assignedServiceIds, $selectedServices);
            if (!empty($unselectedServices)) {
                $inUnselected = implode(',', array_map('intval', $unselectedServices));
                $pdo->exec("UPDATE service SET id_freelance = 0 WHERE id_service IN ($inUnselected)");
            }
        }

        if (!empty($selectedServices)) {
            $inSelected = implode(',', array_map('intval', $selectedServices));
            $pdo->exec("UPDATE service SET id_freelance = " . (int)$id . " WHERE id_service IN ($inSelected)");
        }

        $_SESSION['flash_msg']  = 'Le freelance « ' . $old['prenom'] . ' ' . $old['nom'] . ' » a été modifié avec succès !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/admin/freelances/index.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';

// Chemin image actuelle
$imgUrl = null;
if (!empty($old['image'])) {
    $imgFile = __DIR__ . '/../../assets/uploads/freelances/' . $old['image'];
    if (file_exists($imgFile)) {
        $imgUrl = BASE_URL . '/assets/uploads/freelances/' . htmlspecialchars($old['image']);
    }
}
?>

<!-- En-tête page -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <h1 style="font-size:20px;font-weight:800;margin-bottom:4px;">
            ✏️ Modifier — <?= htmlspecialchars($freelance['prenom'] . ' ' . $freelance['nom']) ?>
        </h1>
        <p style="color:var(--text-muted);font-size:13px;">Modifiez les informations du freelance.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/freelances/index.php" class="btn btn-outline">← Retour à la liste</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    ❌ <strong>Veuillez corriger les erreurs :</strong>
    <ul style="margin:8px 0 0 20px;font-size:13px;">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
    <button class="alert-close" onclick="this.parentElement.remove()">✕</button>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate id="form-modifier-freelance">
<div class="card">

    <div class="card-header">
        <h2 class="card-title"><span class="card-icon">ℹ️</span> Informations personnelles</h2>
        <span style="font-size:12px;color:var(--text-muted);">ID #<?= $id ?></span>
    </div>

    <!-- Section : Photo + infos de base -->
    <div style="display:flex;gap:24px;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;">

        <!-- Preview image -->
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
            <div class="image-preview-box" id="preview-image"
                 onclick="document.getElementById('image').click()"
                 title="Cliquez pour changer la photo">
                <?php if ($imgUrl): ?>
                    <img src="<?= $imgUrl ?>" alt="Photo actuelle">
                <?php else: ?>
                    <div class="preview-placeholder">
                        <span class="preview-icon">📷</span>
                        Changer<br>la photo
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($imgUrl): ?>
            <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;color:var(--danger);">
                <input type="checkbox" name="supprimer_image" value="1" id="supprimer_image">
                🗑️ Supprimer la photo
            </label>
            <?php endif; ?>

            <span style="font-size:11px;color:var(--text-muted);">JPG, PNG, WEBP — max 5 MB</span>
            <?php if (!empty($errors['image'])): ?>
            <span class="form-error">⚠ <?= htmlspecialchars($errors['image']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Champs -->
        <div class="form-grid" style="flex:1;">

            <div class="form-group">
                <label for="prenom">Prénom <span class="required">*</span></label>
                <input type="text" name="prenom" id="prenom"
                       value="<?= htmlspecialchars($old['prenom']) ?>" required>
                <?php if (!empty($errors['prenom'])): ?>
                <span class="form-error">⚠ <?= htmlspecialchars($errors['prenom']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="nom">Nom <span class="required">*</span></label>
                <input type="text" name="nom" id="nom"
                       value="<?= htmlspecialchars($old['nom']) ?>" required>
                <?php if (!empty($errors['nom'])): ?>
                <span class="form-error">⚠ <?= htmlspecialchars($errors['nom']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" name="email" id="email"
                       value="<?= htmlspecialchars($old['email']) ?>" required>
                <?php if (!empty($errors['email'])): ?>
                <span class="form-error">⚠ <?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" name="telephone" id="telephone"
                       value="<?= htmlspecialchars($old['telephone'] ?? '') ?>">
            </div>

        </div>
    </div>

    <!-- Input image caché -->
    <input type="file" name="image" id="image"
           data-preview="preview-image"
           accept="image/*"
           style="display:none;">

    <!-- Description -->
    <div class="form-group" style="margin-bottom:24px;">
        <label for="description">Description / Bio</label>
        <textarea name="description" id="description" rows="4"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
    </div>

    <div class="divider"></div>

    <div class="card-header" style="border:none;padding-bottom:0;margin-bottom:16px;">
        <h2 class="card-title"><span class="card-icon">🌐</span> Réseaux sociaux</h2>
    </div>

    <div class="form-grid">

        <div class="form-group">
            <label for="facebook">🔵 Facebook</label>
            <input type="url" name="facebook" id="facebook"
                   placeholder="https://facebook.com/profil"
                   value="<?= htmlspecialchars($old['facebook'] ?? '') ?>">
            <?php if (!empty($errors['facebook'])): ?>
            <span class="form-error">⚠ <?= htmlspecialchars($errors['facebook']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="instagram">📷 Instagram</label>
            <input type="url" name="instagram" id="instagram"
                   placeholder="https://instagram.com/profil"
                   value="<?= htmlspecialchars($old['instagram'] ?? '') ?>">
            <?php if (!empty($errors['instagram'])): ?>
            <span class="form-error">⚠ <?= htmlspecialchars($errors['instagram']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="linkedin">💼 LinkedIn</label>
            <input type="url" name="linkedin" id="linkedin"
                   placeholder="https://linkedin.com/in/profil"
                   value="<?= htmlspecialchars($old['linkedin'] ?? '') ?>">
            <?php if (!empty($errors['linkedin'])): ?>
            <span class="form-error">⚠ <?= htmlspecialchars($errors['linkedin']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="github">🐙 GitHub</label>
            <input type="url" name="github" id="github"
                   placeholder="https://github.com/profil"
                   value="<?= htmlspecialchars($old['github'] ?? '') ?>">
            <?php if (!empty($errors['github'])): ?>
            <span class="form-error">⚠ <?= htmlspecialchars($errors['github']) ?></span>
            <?php endif; ?>
        </div>

    </div>

    <div class="divider"></div>

    <!-- Section : Compétences et services -->
    <div class="card-header" style="border:none;padding-bottom:0;margin-bottom:16px;">
        <h2 class="card-title"><span class="card-icon">🎯</span> Compétences et services</h2>
    </div>

    <!-- 1. Catégories de services -->
    <div class="form-group" style="margin-bottom:20px;">
        <label style="font-weight:600;margin-bottom:8px;display:block;">🗂️ Catégories de services</label>
        <?php if (empty($allCategories)): ?>
            <p style="font-size:12px;color:var(--text-muted);">Aucune catégorie enregistrée.</p>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:10px;background:var(--bg-input);padding:14px;border-radius:8px;border:1px solid var(--border);">
                <?php foreach ($allCategories as $cat): ?>
                <?php $checked = in_array((int)$cat['id_categorie'], $selectedCategories, true) ? 'checked' : ''; ?>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);">
                    <input type="checkbox" name="categories[]" value="<?= $cat['id_categorie'] ?>" <?= $checked ?>>
                    <span><?= htmlspecialchars($cat['nom']) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2. Services (Gigs) -->
    <div class="form-group" style="margin-bottom:24px;">
        <label style="font-weight:600;margin-bottom:8px;display:block;">🛠️ Services (Gigs) associés</label>
        <?php if (empty($allServices)): ?>
            <p style="font-size:12px;color:var(--text-muted);">Aucun service disponible.</p>
        <?php else: ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:10px;background:var(--bg-input);padding:14px;border-radius:8px;border:1px solid var(--border);">
                <?php foreach ($allServices as $srv): ?>
                <?php $checked = in_array((int)$srv['id_service'], $selectedServices, true) ? 'checked' : ''; ?>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;color:var(--text-primary);">
                    <input type="checkbox" name="services[]" value="<?= $srv['id_service'] ?>" <?= $checked ?>>
                    <div style="min-width:0;flex:1;">
                        <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= htmlspecialchars($srv['titre']) ?>
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);">
                            <?= htmlspecialchars($srv['nom_categorie'] ?? 'Général') ?> &bull; <?= number_format((float)$srv['prix'], 0, ',', ' ') ?> DH
                        </div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Boutons -->
    <div class="form-actions">
        <button type="submit" class="btn btn-success" id="btn-submit-modifier">
            ✅ Enregistrer les modifications
        </button>
        <a href="<?= BASE_URL ?>/admin/freelances/index.php" class="btn btn-outline">
            ✕ Annuler
        </a>
    </div>

</div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
