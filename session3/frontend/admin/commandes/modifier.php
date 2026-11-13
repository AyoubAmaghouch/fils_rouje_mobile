<?php
/**
 * Modifier le statut d'une Commande — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/CommandeService.php';

$commandeService = new CommandeService();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_URL . '/frontend/admin/commandes/index.php');
    exit;
}

try {
    $commande = $commandeService->getById($id);
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/commandes/index.php');
    exit;
}

$pageTitle  = 'Commande #' . $id . ' — Modifier Statut';
$activePage = 'commandes';
$breadcrumb = [
    ['label' => 'Commandes', 'url' => BASE_URL . '/frontend/admin/commandes/index.php'],
    ['label' => 'Modifier Statut']
];

$old = [
    'statut'     => $commande->getStatut(),
    'prix_total' => $commande->getPrixTotal(),
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old = [
        'statut'     => trim($_POST['statut'] ?? ''),
        'prix_total' => filter_input(INPUT_POST, 'prix_total', FILTER_VALIDATE_FLOAT) ?: 0.0,
    ];

    try {
        $updated = $commandeService->update($id, $old);
        $_SESSION['flash_msg']  = 'La commande #' . $id . ' a été mise à jour (Statut: ' . ucfirst($updated->getStatut()) . ') !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/frontend/admin/commandes/index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $errors['general'] = $e->getMessage();
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
            <span>📦</span> Commande #<?= $id ?>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Mettez à jour le statut et le suivi de facturation de cette commande.</p>
    </div>
    <a href="<?= BASE_URL ?>/frontend/admin/commandes/index.php" class="px-4 py-2 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
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
<form method="POST" novalidate id="form-modifier-commande" class="max-w-2xl">
    <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-6">

        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>📦</span> Suivi de commande
            </h2>
            <span class="text-xs font-mono text-slate-500">Date : <?= date('d/m/Y', strtotime($commande->getDateCommande())) ?></span>
        </div>

        <div class="bg-[#222240] p-4 rounded-xl border border-white/5 space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-400">Prestation :</span>
                <span class="font-bold text-white"><?= htmlspecialchars($commande->getTitreService() ?? '—') ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Freelance :</span>
                <span class="font-medium text-slate-200"><?= htmlspecialchars(trim(($commande->getPrenomFreelance() ?? '') . ' ' . ($commande->getNomFreelance() ?? '')) ?: '—') ?></span>
            </div>
        </div>

        <div>
            <label for="statut" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Statut de la commande <span class="text-red-400">*</span>
            </label>
            <select name="statut" id="statut" required
                    class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                <option value="en attente" <?= $old['statut'] === 'en attente' ? 'selected' : '' ?>>⏳ En attente (Reçue)</option>
                <option value="confirmée"  <?= $old['statut'] === 'confirmée'  ? 'selected' : '' ?>>☑️ Confirmée (Prête)</option>
                <option value="en cours"   <?= $old['statut'] === 'en cours'   ? 'selected' : '' ?>>⚙️ En cours (En réalisation)</option>
                <option value="terminée"   <?= $old['statut'] === 'terminée'   ? 'selected' : '' ?>>✅ Terminée (Livrée)</option>
                <option value="annulée"    <?= $old['statut'] === 'annulée'    ? 'selected' : '' ?>>❌ Annulée</option>
            </select>
        </div>

        <div>
            <label for="prix_total" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                Prix total facturé (DH) <span class="text-red-400">*</span>
            </label>
            <input type="number" name="prix_total" id="prix_total" step="0.01" min="0" required
                   value="<?= htmlspecialchars((string)$old['prix_total']) ?>"
                   class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
        </div>

        <div class="pt-4 border-t border-white/5 flex items-center gap-3">
            <button type="submit" id="btn-submit-modifier-commande"
                    data-loading-text="Mise à jour en cours..."
                    class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                <span>✅</span> Enregistrer le statut
            </button>
            <a href="<?= BASE_URL ?>/frontend/admin/commandes/index.php" class="px-4 py-2.5 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
                ✕ Annuler
            </a>
        </div>

    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
