<?php
/**
 * Supprimer une Commande — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pdo = getDB();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/commandes/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM commande WHERE id_commande = ?");
$stmt->execute([$id]);
$commande = $stmt->fetch();

if (!$commande) {
    $_SESSION['flash_msg']  = 'Commande introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/commandes/index.php');
    exit;
}

try {
    $del = $pdo->prepare("DELETE FROM commande WHERE id_commande = ?");
    $del->execute([$id]);

    $_SESSION['flash_msg']  = 'La commande #' . $id . ' a été supprimée avec succès.';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/admin/commandes/index.php');
exit;
