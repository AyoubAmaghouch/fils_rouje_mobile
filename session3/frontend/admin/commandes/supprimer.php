<?php
/**
 * Supprimer une Commande — Admin (Refactorisé OOP)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/CommandeService.php';

$commandeService = new CommandeService();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/commandes/index.php');
    exit;
}

try {
    $commandeService->getById($id);
    $commandeService->delete($id);

    $_SESSION['flash_msg']  = 'La commande #' . $id . ' a été supprimée avec succès.';
    $_SESSION['flash_type'] = 'success';
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/frontend/admin/commandes/index.php');
exit;
