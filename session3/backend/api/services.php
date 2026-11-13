<?php
/**
 * API Endpoint : Services / Gigs
 * Gère les requêtes REST pour les services.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ServiceController.php';

$controller = new ServiceController();
$controller->handleRequest();

