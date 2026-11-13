<?php
/**
 * API Endpoint : Freelances
 * Gère les requêtes REST pour les freelances.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/FreelanceController.php';

$controller = new FreelanceController();
$controller->handleRequest();

