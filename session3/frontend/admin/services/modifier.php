<?php
/**
 * Modifier un Service (Gig) — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/ServiceService.php';
require_once __DIR__ . '/../../../backend/dao/FreelanceDAO.php';
require_once __DIR__ . '/../../../backend/dao/CategorieDAO.php';

$serviceService = new ServiceService();
$freelanceDAO   = new FreelanceDAO();
$categorieDAO   = new CategorieDAO();
$errors         = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_URL . '/frontend/admin/services/index.php');
    exit;
}

try {
    $service = $serviceService->getById($id);
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Service introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/services/index.php');
    exit;
}

$pageTitle  = 'Modifier — ' . htmlspecialchars($service->getTitre());
$activePage = 'services';
$breadcrumb = [
    ['label' => 'Services', 'url' => BASE_URL . '/frontend/admin/services/index.php'],
    ['label' => 'Modifier']
];

$freelances = $freelanceDAO->findAll();
$categories = $categorieDAO->findAll();

$old = [
    'titre'         => $service->getTitre(),
    'id_freelance'  => $service->getIdFreelance(),
    'id_categorie'  => $service->getIdCategorie(),
    'prix'          => $service->getPrix(),
    'description'   => $service->getDescription(),
    'image_service' => $service->getImageService(),
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old = [
        'titre'         => trim($_POST['titre'] ?? ''),
        'id_freelance'  => filter_input(INPUT_POST, 'id_freelance', FILTER_VALIDATE_INT) ?: 0,
        'id_categorie'  => filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT) ?: 0,
        'prix'          => filter_input(INPUT_POST, 'prix', FILTER_VALIDATE_FLOAT) ?: 0.0,
        'description'   => trim($_POST['description'] ?? ''),
        'image_service' => $service->getImageService(),
    ];
    $supprimerImage = isset($_POST['supprimer_image']) && $_POST['supprimer_image'] === '1';

    try {
        $file = $_FILES['image_service'] ?? null;
        $updated = $serviceService->update($id, $old, $file, $supprimerImage);

        $_SESSION['flash_msg']  = 'Le service « ' . $updated->getTitre() . ' » a été modifié avec succès !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/frontend/admin/services/index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $errors['general'] = $e->getMessage();
    } catch (Throwable $e) {
        $errors['general'] = "Erreur inattendue : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../../includes/header.php';

// Image actuelle
$imgUrl = null;
if (!empty($old['image_service'])) {
    $imgFile = __DIR__ . '/../../assets/uploads/services/' . $old['image_service'];
    if (file_exists($imgFile)) {
        $imgUrl = BASE_URL . '/frontend/assets/uploads/services/' . htmlspecialchars($old['image_service']);
    }
}
?>

<!-- En-tête page -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-extrabold text-white flex items-center gap-2">
            <span>✏️</span> Modifier le Service
        </h1>
        <p class="text-xs text-slate-400 mt-1">Mettez à jour les conditions et détails de cette prestation.</p>
    </div>
    <a href="<?= BASE_URL ?>/frontend/admin/services/index.php" class="px-4 py-2 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
        ← Retour à la liste
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300" data-alert>
    <div class="flex items-center justify-between mb-2">
        <div class="font-bold text-sm">❌ Veuillez corriger les erreurs suivantes :</div>
        <button type="button" class="text-slate-400 hover:text-white" data-alert-close>✕</button>
    </div>
    <ul class="list-disc list-inside text-xs space-y-1">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Formulaire -->
<form method="POST" enctype="multipart/form-data" novalidate id="form-modifier-service" class="max-w-3xl">
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">

        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>🛠️</span> Informations du service
            </h2>
            <span class="text-xs font-mono text-slate-500">ID #<?= $id ?></span>
        </div>

        <div>
            <label for="titre" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Titre du service <span class="text-red-400">*</span>
            </label>
            <input type="text" name="titre" id="titre" required minlength="3"
                   value="<?= htmlspecialchars($old['titre']) ?>"
                   class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="id_freelance" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Freelance prestataire <span class="text-red-400">*</span>
                </label>
                <select name="id_freelance" id="id_freelance" required
                        class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    <?php foreach ($freelances as $f): ?>
                    <?php
                        $fid = is_object($f) ? $f->getIdFreelance() : $f['id_freelance'];
                        $fnom = is_object($f) ? $f->getNomComplet() : ($f['prenom'] . ' ' . $f['nom']);
                    ?>
                    <option value="<?= $fid ?>" <?= $old['id_freelance'] === $fid ? 'selected' : '' ?>>
                        <?= htmlspecialchars($fnom) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="id_categorie" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Catégorie <span class="text-red-400">*</span>
                </label>
                <select name="id_categorie" id="id_categorie" required
                        class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    <?php foreach ($categories as $cat): ?>
                    <?php
                        $cid = is_object($cat) ? $cat->getIdCategorie() : $cat['id_categorie'];
                        $cnom = is_object($cat) ? $cat->getNom() : $cat['nom'];
                    ?>
                    <option value="<?= $cid ?>" <?= $old['id_categorie'] === $cid ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cnom) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label for="prix" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Prix unitaire (DH) <span class="text-red-400">*</span>
            </label>
            <input type="number" name="prix" id="prix" step="0.01" min="1" required
                   value="<?= htmlspecialchars((string)$old['prix']) ?>"
                   class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Description détaillée <span class="text-red-400">*</span>
            </label>
            <textarea name="description" id="description" rows="5" required minlength="10"
                      class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors"><?= htmlspecialchars($old['description']) ?></textarea>
        </div>

        <!-- Image de présentation -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Image illustrative du service
            </label>
            <div class="flex items-center gap-4">
                <div id="service-preview" class="w-24 h-18 rounded-xl bg-[#222240] border border-dashed border-white/10 flex items-center justify-center overflow-hidden">
                    <?php if ($imgUrl): ?>
                        <img src="<?= $imgUrl ?>" alt="Aperçu" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-slate-500 text-2xl">🖼️</span>
                    <?php endif; ?>
                </div>

                <div class="space-y-2">
                    <div>
                        <input type="file" name="image_service" id="image_service" accept="image/*" data-preview="service-preview" class="hidden">
                        <label for="image_service" class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-xs font-semibold cursor-pointer transition-colors inline-block">
                            📁 Changer la vignette
                        </label>
                    </div>
                    <?php if ($imgUrl): ?>
                    <label class="flex items-center gap-2 text-xs text-red-400 hover:text-red-300 cursor-pointer">
                        <input type="checkbox" name="supprimer_image" value="1" class="rounded border-white/20 bg-white/5 text-red-600 focus:ring-0">
                        <span>Supprimer la vignette actuelle</span>
                    </label>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-white/5 flex items-center gap-3">
            <button type="submit" id="btn-submit-modifier-service"
                    data-loading-text="Mise à jour en cours..."
                    class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                <span>✅</span> Enregistrer les modifications
            </button>
            <a href="<?= BASE_URL ?>/frontend/admin/services/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
                ✕ Annuler
            </a>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
