<?php
/**
 * Modifier un Freelance — Admin (Refactorisé OOP + Tailwind CSS)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/FreelanceService.php';
require_once __DIR__ . '/../../../backend/dao/ServiceDAO.php';

$freelanceService = new FreelanceService();
$serviceDAO       = new ServiceDAO();
$errors           = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . BASE_URL . '/frontend/admin/freelances/index.php');
    exit;
}

try {
    $freelance = $freelanceService->getById($id);
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Freelance introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/freelances/index.php');
    exit;
}

$pageTitle  = 'Modifier — ' . htmlspecialchars($freelance->getNomComplet());
$activePage = 'freelances';
$breadcrumb = [
    ['label' => 'Freelances', 'url' => BASE_URL . '/frontend/admin/freelances/index.php'],
    ['label' => 'Modifier']
];

$allServices = $serviceDAO->findAll();
$assignedServices = $serviceDAO->findByFreelance($id);
$assignedServiceIds = array_map(fn($s) => $s->getIdService(), $assignedServices);

$old = [
    'nom'         => $freelance->getNom(),
    'prenom'      => $freelance->getPrenom(),
    'email'       => $freelance->getEmail(),
    'telephone'   => $freelance->getTelephone() ?? '',
    'description' => $freelance->getDescription() ?? '',
    'facebook'    => $freelance->getFacebook() ?? '',
    'instagram'   => $freelance->getInstagram() ?? '',
    'linkedin'    => $freelance->getLinkedin() ?? '',
    'github'      => $freelance->getGithub() ?? '',
    'image'       => $freelance->getImage(),
];

$selectedServices = $assignedServiceIds;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $old = [
        'nom'         => trim($_POST['nom'] ?? ''),
        'prenom'      => trim($_POST['prenom'] ?? ''),
        'email'       => trim($_POST['email'] ?? ''),
        'telephone'   => trim($_POST['telephone'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'facebook'    => trim($_POST['facebook'] ?? ''),
        'instagram'   => trim($_POST['instagram'] ?? ''),
        'linkedin'    => trim($_POST['linkedin'] ?? ''),
        'github'      => trim($_POST['github'] ?? ''),
        'image'       => $freelance->getImage(),
    ];
    $selectedServices = array_map('intval', $_POST['services'] ?? []);
    $supprimerImage = isset($_POST['supprimer_image']) && $_POST['supprimer_image'] === '1';

    try {
        $file = $_FILES['image'] ?? null;
        $updated = $freelanceService->update($id, $old, $file, $supprimerImage, $selectedServices, true);

        $_SESSION['flash_msg']  = 'Le freelance « ' . $updated->getNomComplet() . ' » a été modifié avec succès !';
        $_SESSION['flash_type'] = 'success';
        header('Location: ' . BASE_URL . '/frontend/admin/freelances/index.php');
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
if (!empty($old['image'])) {
    $imgFile = __DIR__ . '/../../assets/uploads/freelances/' . $old['image'];
    if (file_exists($imgFile)) {
        $imgUrl = BASE_URL . '/frontend/assets/uploads/freelances/' . htmlspecialchars($old['image']);
    }
}
?>

<!-- En-tête page -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-extrabold text-white flex items-center gap-2">
            <span>✏️</span> Modifier le Freelance
        </h1>
        <p class="text-xs text-slate-400 mt-1">Mettez à jour les informations du profil professionnel ci-dessous.</p>
    </div>
    <a href="<?= BASE_URL ?>/frontend/admin/freelances/index.php" class="px-4 py-2 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
        ← Retour à la liste
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300" data-alert>
    <div class="flex items-center justify-between mb-2">
        <div class="font-bold text-sm">❌ Erreurs détectées :</div>
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
<form method="POST" enctype="multipart/form-data" novalidate id="form-modifier-freelance">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Colonne Principale (2/3) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Section 1 : Identité -->
            <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-white/5 pb-3">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span>👤</span> Identité & Contact
                    </h2>
                    <span class="text-xs font-mono text-slate-500">ID #<?= $id ?></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prenom" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Prénom <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="prenom" id="prenom" required minlength="2"
                               value="<?= htmlspecialchars($old['prenom']) ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                    <div>
                        <label for="nom" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Nom <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="nom" id="nom" required minlength="2"
                               value="<?= htmlspecialchars($old['nom']) ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Email professionnel <span class="text-red-400">*</span>
                        </label>
                        <input type="email" name="email" id="email" required
                               value="<?= htmlspecialchars($old['email']) ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                    <div>
                        <label for="telephone" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            Téléphone
                        </label>
                        <input type="tel" name="telephone" id="telephone"
                               value="<?= htmlspecialchars($old['telephone']) ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Biographie / Description
                    </label>
                    <textarea name="description" id="description" rows="4"
                              class="w-full px-4 py-2.5 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors"><?= htmlspecialchars($old['description']) ?></textarea>
                </div>
            </div>

            <!-- Section 2 : Réseaux Sociaux -->
            <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="border-b border-white/5 pb-3">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🌐</span> Liens & Réseaux Sociaux
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="linkedin" class="block text-xs font-semibold text-slate-300 mb-1.5">💼 LinkedIn</label>
                        <input type="url" name="linkedin" id="linkedin"
                               value="<?= htmlspecialchars($old['linkedin']) ?>"
                               class="w-full px-4 py-2 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                    <div>
                        <label for="github" class="block text-xs font-semibold text-slate-300 mb-1.5">🐙 GitHub</label>
                        <input type="url" name="github" id="github"
                               value="<?= htmlspecialchars($old['github']) ?>"
                               class="w-full px-4 py-2 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                    <div>
                        <label for="facebook" class="block text-xs font-semibold text-slate-300 mb-1.5">📘 Facebook</label>
                        <input type="url" name="facebook" id="facebook"
                               value="<?= htmlspecialchars($old['facebook']) ?>"
                               class="w-full px-4 py-2 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                    <div>
                        <label for="instagram" class="block text-xs font-semibold text-slate-300 mb-1.5">📸 Instagram</label>
                        <input type="url" name="instagram" id="instagram"
                               value="<?= htmlspecialchars($old['instagram']) ?>"
                               class="w-full px-4 py-2 rounded-xl bg-[#222240] border border-white/10 text-white text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex items-center gap-3">
                <button type="submit" id="btn-submit-modifier-freelance" data-loading-text="Mise à jour en cours..."
                        class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                    <span>✅</span> Enregistrer les modifications
                </button>
                <a href="<?= BASE_URL ?>/frontend/admin/freelances/index.php" class="px-5 py-3 rounded-xl border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 text-sm font-medium transition-colors">
                    ✕ Annuler
                </a>
            </div>

        </div>

        <!-- Colonne Latérale (1/3) -->
        <div class="space-y-6">

            <!-- Photo de Profil -->
            <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-4 text-center">
                <h3 class="text-sm font-bold text-white text-left flex items-center gap-2 border-b border-white/5 pb-3">
                    <span>📸</span> Photo de profil
                </h3>

                <div id="avatar-preview" class="w-32 h-32 rounded-2xl bg-[#222240] border-2 border-dashed border-white/10 mx-auto flex items-center justify-center overflow-hidden">
                    <?php if ($imgUrl): ?>
                        <img src="<?= $imgUrl ?>" alt="Aperçu" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-slate-500 text-3xl">👤</span>
                    <?php endif; ?>
                </div>

                <?php if ($imgUrl): ?>
                <label class="flex items-center justify-center gap-2 text-xs text-red-400 hover:text-red-300 cursor-pointer">
                    <input type="checkbox" name="supprimer_image" value="1" class="rounded border-white/20 bg-white/5 text-red-600 focus:ring-0">
                    <span>Supprimer la photo actuelle</span>
                </label>
                <?php endif; ?>

                <div>
                    <input type="file" name="image" id="image" accept="image/*" data-preview="avatar-preview" class="hidden">
                    <label for="image" class="inline-block px-4 py-2 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-xs font-semibold cursor-pointer transition-colors">
                        📁 Changer la photo
                    </label>
                    <p class="text-[11px] text-slate-500 mt-2">JPG, PNG, GIF, WEBP max 5 Mo.</p>
                </div>
            </div>

            <!-- Services Associés -->
            <?php if (!empty($allServices)): ?>
            <div class="bg-[#18182e] border border-white/5 rounded-2xl p-6 shadow-xl space-y-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-white/5 pb-3">
                    <span>🛠️</span> Services Associés
                </h3>
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    <?php foreach ($allServices as $s): ?>
                    <?php
                        $sid = is_object($s) ? $s->getIdService() : $s['id_service'];
                        $stitre = is_object($s) ? $s->getTitre() : $s['titre'];
                        $sprix = is_object($s) ? $s->getPrix() : $s['prix'];
                    ?>
                    <label class="flex items-center gap-3 p-2.5 bg-[#222240] rounded-xl border border-white/5 hover:border-white/10 cursor-pointer transition-colors text-xs">
                        <input type="checkbox" name="services[]" value="<?= $sid ?>"
                               <?= in_array($sid, $selectedServices, true) ? 'checked' : '' ?>
                               class="rounded border-white/20 bg-white/5 text-indigo-600 focus:ring-0">
                        <span class="flex-1 text-slate-200 font-medium truncate"><?= htmlspecialchars($stitre) ?></span>
                        <span class="text-indigo-400 font-bold"><?= number_format((float)$sprix, 0) ?> DH</span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
