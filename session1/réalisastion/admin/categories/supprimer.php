<?php
/**
 * Supprimer une Catégorie — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pdo = getDB();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/categories/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categorie_service WHERE id_categorie = ?");
$stmt->execute([$id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    $_SESSION['flash_msg']  = 'Catégorie introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/categories/index.php');
    exit;
}

try {
    $del = $pdo->prepare("DELETE FROM categorie_service WHERE id_categorie = ?");
    $del->execute([$id]);

    $_SESSION['flash_msg']  = 'La catégorie « ' . $categorie['nom'] . ' » a été supprimée avec succès.';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/admin/categories/index.php');
exit;
