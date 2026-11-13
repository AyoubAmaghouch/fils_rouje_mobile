<?php
/**
 * API Endpoint : Catégories de Services
 * Gère les requêtes REST pour les catégories.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/CategorieController.php';

$controller = new CategorieController();
$controller->handleRequest();

