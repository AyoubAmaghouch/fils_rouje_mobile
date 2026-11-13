<?php
/**
 * Supprimer une Catégorie — Admin (Refactorisé OOP)
 */

if (!defined('ADMIN_ACCESS')) { define('ADMIN_ACCESS', true); }

require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/services/CategorieService.php';

$categorieService = new CategorieService();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/admin/categories/index.php');
    exit;
}

try {
    $categorie = $categorieService->getById($id);
    $nom = $categorie->getNom();

    $categorieService->delete($id);

    $_SESSION['flash_msg']  = 'La catégorie « ' . $nom . ' » a été supprimée avec succès.';
    $_SESSION['flash_type'] = 'success';
} catch (Throwable $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/frontend/admin/categories/index.php');
exit;
