<?php
/**
 * Supprimer un Freelance — Admin (Refactorisé OOP)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/FreelanceService.php';

$freelanceService = new FreelanceService();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/freelances/index.php');
    exit;
}

try {
    $freelance = $freelanceService->getById($id);
    $nomComplet = $freelance->getNomComplet();

    $freelanceService->delete($id);

    $_SESSION['flash_msg']  = 'Le freelance « ' . $nomComplet . ' » a été supprimé avec succès.';
    $_SESSION['flash_type'] = 'success';
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/frontend/admin/freelances/index.php');
exit;
