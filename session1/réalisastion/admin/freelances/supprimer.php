<?php
/**
 * Supprimer un Freelance — Admin
 * Suppression sécurisée avec suppression de l'image associée
 */

define('ADMIN_ACCESS', true);

require_once __DIR__ . '/../../config/database.php';

$pdo = getDB();

// Valider l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['flash_msg']  = 'Identifiant invalide.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/freelances/index.php');
    exit;
}

// Récupérer le freelance (pour supprimer l'image et afficher le nom)
$stmt = $pdo->prepare("SELECT * FROM freelance WHERE id_freelance = ?");
$stmt->execute([$id]);
$freelance = $stmt->fetch();

if (!$freelance) {
    $_SESSION['flash_msg']  = 'Freelance introuvable.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . BASE_URL . '/admin/freelances/index.php');
    exit;
}

try {
    // Supprimer l'image du serveur si elle existe
    if (!empty($freelance['image'])) {
        $imgPath = __DIR__ . '/../../assets/uploads/freelances/' . $freelance['image'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    // Supprimer le freelance (CASCADE supprimera aussi ses services et commandes)
    $del = $pdo->prepare("DELETE FROM freelance WHERE id_freelance = ?");
    $del->execute([$id]);

    $nom = $freelance['prenom'] . ' ' . $freelance['nom'];
    $_SESSION['flash_msg']  = 'Le freelance « ' . $nom . ' » a été supprimé avec succès.';
    $_SESSION['flash_type'] = 'success';

} catch (PDOException $e) {
    $_SESSION['flash_msg']  = 'Erreur lors de la suppression : ' . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

header('Location: ' . BASE_URL . '/admin/freelances/index.php');
exit;
