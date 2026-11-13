<?php
/**
 * API Router — Point d'entrée principal des API REST
 *
 * Utilisation :
 *   GET    /backend/api/index.php?resource=freelances
 *   GET    /backend/api/index.php?resource=freelances&id=1
 *   POST   /backend/api/index.php?resource=freelances
 *   PUT    /backend/api/index.php?resource=freelances&id=1
 *   DELETE /backend/api/index.php?resource=freelances&id=1
 *
 * Ressources disponibles :
 *   - freelances
 *   - categories
 *   - services
 *   - commandes
 *   - dashboard
 *   - classification
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/FreelanceController.php';
require_once __DIR__ . '/../controllers/CategorieController.php';
require_once __DIR__ . '/../controllers/ServiceController.php';
require_once __DIR__ . '/../controllers/CommandeController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

// Extraction de la ressource demandée via query string ou PATH_INFO
$resource = $_GET['resource'] ?? '';
if (empty($resource) && !empty($_SERVER['PATH_INFO'])) {
    $parts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    $resource = $parts[0] ?? '';
}
$resource = strtolower(trim($resource));

switch ($resource) {
    case 'freelances':
    case 'freelance':
        $controller = new FreelanceController();
        $controller->handleRequest();
        break;

    case 'categories':
    case 'categorie':
        $controller = new CategorieController();
        $controller->handleRequest();
        break;

    case 'services':
    case 'service':
        $controller = new ServiceController();
        $controller->handleRequest();
        break;

    case 'commandes':
    case 'commande':
        $controller = new CommandeController();
        $controller->handleRequest();
        break;

    case 'dashboard':
    case 'stats':
        $controller = new DashboardController();
        $controller->handleRequest();
        break;

    case 'classification':
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data'    => ClassificationService::load()
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;

    default:
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'status'  => 404,
            'message' => "Ressource « {$resource} » non trouvée. Ressources valides : freelances, categories, services, commandes, dashboard, classification.",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
}

