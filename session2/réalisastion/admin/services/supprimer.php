<?php
/**
 * Supprimer un Service — Admin
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pdo = getDB();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/services/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM service WHERE id_service = ?");
$stmt->execute([$id]);
$service = $stmt->fetch();

if (!$service) {
    $_SESSION['flash_msg']  = 'Service introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/services/index.php');
    exit;
}

try {
    if (!empty($service['image_service'])) {
        $imgPath = __DIR__ . '/../../assets/uploads/services/' . $service['image_service'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    $del = $pdo->prepare("DELETE FROM service WHERE id_service = ?");
    $del->execute([$id]);

    $_SESSION['flash_msg']  = 'Le service « ' . $service['titre'] . ' » a été supprimé avec succès.';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/admin/services/index.php');
exit;
