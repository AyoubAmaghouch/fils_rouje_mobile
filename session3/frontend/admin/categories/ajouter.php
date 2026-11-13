<?php
/**
 * Ajouter une Catégorie de Service — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/CategorieService.php';

$pageTitle  = 'Ajouter une Catégorie';
$activePage = 'categories';
$breadcrumb = [
    ['label' => 'Catégories', 'url' => BASE_URL . '/frontend/admin/categories/index.php'],
    ['label' => 'Ajouter']
];

$categorieService = new CategorieService();
$errors = [];
$old    = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old['nom']         = trim($_POST['nom'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');

    try {
        $newCategory = $categorieService->create($old);
        $_SESSION['flash_msg']  = 'La catégorie « ' . $newCategory->getNom() . ' » a été ajoutée avec succès !';
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
            <span>➕</span> Ajouter une Catégorie
        </h1>
        <p class="text-xs text-slate-400 mt-1">Remplissez les informations ci-dessous.</p>
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
<form method="POST" novalidate id="form-ajouter-categorie" class="max-w-2xl">
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">

        <div class="border-b border-white/5 pb-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>🗂️</span> Informations de la catégorie
            </h2>
        </div>

        <div>
            <label for="nom" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Nom de la catégorie <span class="text-red-400">*</span>
            </label>
            <input type="text" name="nom" id="nom"
                   placeholder="ex : Développement Web, Design Graphique..."
                   value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required minlength="2"
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
                      placeholder="Décrivez brièvement le type de services regroupés sous cette catégorie..."
                      class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 transition-colors"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
        </div>

        <div class="pt-4 border-t border-white/5 flex items-center gap-3">
            <button type="submit" id="btn-submit-categorie" data-loading-text="Enregistrement en cours..."
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                <span>✅</span> Enregistrer la Catégorie
            </button>
            <a href="<?= BASE_URL ?>/frontend/admin/categories/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
                ✕ Annuler
            </a>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
