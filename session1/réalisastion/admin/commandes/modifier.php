<?php
/**
 * Modifier le statut d'une Commande — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pdo = getDB();
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_URL . '/admin/commandes/index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.*, s.titre AS titre_service, f.nom AS nom_freelance, f.prenom AS prenom_freelance
    FROM commande c
    LEFT JOIN service s ON c.id_service = s.id_service
    LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
    WHERE c.id_commande = ?
");
$stmt->execute([$id]);
$commande = $stmt->fetch();

if (!$commande) {
    $_SESSION['flash_msg']  = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/commandes/index.php');
    exit;
}

$pageTitle  = 'Commande #' . $id . ' — Modifier Statut';
$activePage = 'commandes';
$breadcrumb = [
    ['label' => 'Commandes', 'url' => BASE_URL . '/admin/commandes/index.php'],
    ['label' => 'Modifier Statut']
];

$old = $commande;

$statutsValides = ['en attente', 'confirmée', 'en cours', 'terminée', 'annulée'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['statut']     = trim($_POST['statut'] ?? '');
    $old['prix_total'] = filter_input(INPUT_POST, 'prix_total', FILTER_VALIDATE_FLOAT);

    if (!in_array($old['statut'], $statutsValides)) {
        $errors['statut'] = 'Veuillez sélectionner un statut valide.';
    }
    if ($old['prix_total'] === false || $old['prix_total'] < 0) {
        $errors['prix_total'] = 'Le prix total doit être un montant valide.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE commande SET statut = :statut, prix_total = :prix_total WHERE id_commande = :id
        ");
        $stmt->execute([
            ':statut'     => $old['statut'],
            ':prix_total' => $old['prix_total'],
            ':id'         => $id
        ]);

        $_SESSION['flash_msg']  = 'La commande #' . $id . ' a été mise à jour (Statut: ' . ucfirst($old['statut']) . ') !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/admin/commandes/index.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- En-tête page -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
    <div>
        <h1 style="font-size:20px;font-weight:800;margin-bottom:4px;">📦 Commande #<?= $id ?></h1>
        <p style="color:var(--text-muted);font-size:13px;">Modifier le statut de la commande.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/commandes/index.php" class="btn btn-outline">← Retour à la liste</a>
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
<form method="POST" novalidate id="form-modifier-commande">
<div class="card" style="max-width:700px;">

    <div class="card-header">
        <h2 class="card-title"><span class="card-icon">ℹ️</span> Détails de la commande</h2>
        <span style="font-size:12px;color:var(--text-muted);">Date: <?= date('d/m/Y', strtotime($commande['date_commande'])) ?></span>
    </div>

    <!-- Récapitulatif non-modifiable -->
    <div style="background:var(--bg-input);padding:16px;border-radius:8px;margin-bottom:24px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px;">
            <div>
                <span style="color:var(--text-muted);display:block;font-size:11px;text-transform:uppercase;">Service :</span>
                <strong><?= htmlspecialchars($commande['titre_service'] ?? 'Non spécifié') ?></strong>
            </div>
            <div>
                <span style="color:var(--text-muted);display:block;font-size:11px;text-transform:uppercase;">Freelance :</span>
                <strong><?= htmlspecialchars(($commande['prenom_freelance'] ?? '') . ' ' . ($commande['nom_freelance'] ?? '')) ?></strong>
            </div>
        </div>
    </div>

    <!-- Statut -->
    <div class="form-group" style="margin-bottom:20px;">
        <label for="statut">Statut de la commande <span class="required">*</span></label>
        <select name="statut" id="statut" required style="font-size:14px;padding:12px;">
            <option value="en attente" <?= $old['statut'] === 'en attente' ? 'selected' : '' ?>>⏳ En attente</option>
            <option value="confirmée"  <?= $old['statut'] === 'confirmée'  ? 'selected' : '' ?>>☑️ Confirmée</option>
            <option value="en cours"   <?= $old['statut'] === 'en cours'   ? 'selected' : '' ?>>⚙️ En cours</option>
            <option value="terminée"   <?= $old['statut'] === 'terminée'   ? 'selected' : '' ?>>✅ Terminée</option>
            <option value="annulée"    <?= $old['statut'] === 'annulée'    ? 'selected' : '' ?>>❌ Annulée</option>
        </select>
        <?php if (!empty($errors['statut'])): ?>
        <span class="form-error">⚠ <?= htmlspecialchars($errors['statut']) ?></span>
        <?php endif; ?>
    </div>

    <!-- Prix Total -->
    <div class="form-group" style="margin-bottom:24px;">
        <label for="prix_total">Prix Total (DH) <span class="required">*</span></label>
        <input type="number" name="prix_total" id="prix_total" step="0.01" min="0"
               value="<?= htmlspecialchars($old['prix_total']) ?>" required>
        <?php if (!empty($errors['prix_total'])): ?>
        <span class="form-error">⚠ <?= htmlspecialchars($errors['prix_total']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success" id="btn-submit-commande">
            ✅ Enregistrer la mise à jour
        </button>
        <a href="<?= BASE_URL ?>/admin/commandes/index.php" class="btn btn-outline">
            ✕ Annuler
        </a>
    </div>

</div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
