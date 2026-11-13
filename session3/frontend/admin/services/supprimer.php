<?php
/**
 * Supprimer un Service — Admin (Refactorisé OOP)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/ServiceService.php';

$serviceService = new ServiceService();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/services/index.php');
    exit;
}

try {
    $service = $serviceService->getById($id);
    $titre = $service->getTitre();

    $serviceService->delete($id);

    $_SESSION['flash_msg']  = 'Le service « ' . $titre . ' » a été supprimé avec succès.';
    $_SESSION['flash_type'] = 'success';
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/frontend/admin/services/index.php');
exit;
