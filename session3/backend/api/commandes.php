<?php
/**
 * API Endpoint : Commandes
 * Gère les requêtes REST pour les commandes.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/CommandeController.php';

$controller = new CommandeController();
$controller->handleRequest();

