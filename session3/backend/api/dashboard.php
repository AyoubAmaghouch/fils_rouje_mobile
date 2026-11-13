<?php
/**
 * API Endpoint : Dashboard
 * Retourne les statistiques et métriques consolidées.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

$controller = new DashboardController();
$controller->handleRequest();

