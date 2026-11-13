<?php
/**
 * API Endpoint : Classification JSON
 * Retourne les macro-domaines, grilles tarifaires et niveaux.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/ClassificationService.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    echo json_encode([
        'success' => true,
        'status'  => 200,
        'data'    => ClassificationService::load()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status'  => 500,
        'message' => "Erreur lors du chargement des classifications."
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

