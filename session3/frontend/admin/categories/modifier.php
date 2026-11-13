<?php
/**
 * Modifier une Catégorie — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/CategorieService.php';

$categorieService = new CategorieService();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_URL . '/frontend/admin/categories/index.php');
    exit;
}

try {
    $categorie = $categorieService->getById($id);
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Catégorie introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/categories/index.php');
    exit;
}

$pageTitle  = 'Modifier — ' . htmlspecialchars($categorie->getNom());
$activePage = 'categories';
$breadcrumb = [
    ['label' => 'Catégories', 'url' => BASE_URL . '/frontend/admin/categories/index.php'],
    ['label' => 'Modifier']
];

$old = [
    'nom'         => $categorie->getNom(),
    'description' => $categorie->getDescription() ?? '',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old['nom']         = trim($_POST['nom'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');

    try {
        $updatedCategory = $categorieService->update($id, $old);
        $_SESSION['flash_msg']  = 'La catégorie « ' . $updatedCategory->getNom() . ' » a été modifiée avec succès !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/frontend/admin/categories/index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $errors['nom'] = $e->getMessage();
    } catch (Throwable $e) {
        $errors['general'] = "Erreur inattendue : " . $e->getMessage();
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- En-tête page -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-extrabold text-white flex items-center gap-2">
            <span>✏️</span> Modifier la Catégorie
        </h1>
        <p class="text-xs text-slate-400 mt-1">Modifiez les informations ci-dessous.</p>
    </div>
    <a href="<?= BASE_URL ?>/frontend/admin/categories/index.php" class="px-4 py-2 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
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
<form method="POST" novalidate id="form-modifier-categorie" class="max-w-2xl">
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">

        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>🗂️</span> Informations de la catégorie
            </h2>
            <span class="text-xs font-mono text-slate-500">ID #<?= $id ?></span>
        </div>

        <div>
            <label for="nom" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Nom de la catégorie <span class="text-red-400">*</span>
            </label>
            <input type="text" name="nom" id="nom"
                   value="<?= htmlspecialchars($old['nom']) ?>" required minlength="2"
                   class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border <?= !empty($errors['nom']) ? 'border-red-500' : 'border-white/10' ?> text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
            <?php if (!empty($errors['nom'])): ?>
            <p class="text-xs text-red-400 mt-1.5 flex items-center gap-1">⚠ <?= htmlspecialchars($errors['nom']) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Description (optionnel)
            </label>
            <textarea name="description" id="description" rows="4"
                      class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 transition-colors"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>

        <div class="pt-4 border-t border-white/5 flex items-center gap-3">
            <button type="submit" id="btn-submit-modifier-cat" data-loading-text="Mise à jour en cours..."
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                <span>✅</span> Enregistrer les modifications
            </button>
            <a href="<?= BASE_URL ?>/frontend/admin/categories/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
                ✕ Annuler
            </a>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
