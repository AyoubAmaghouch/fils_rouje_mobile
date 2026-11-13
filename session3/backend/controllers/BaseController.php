<?php
/**
 * BaseController — Contrôleur de base pour la gestion des requêtes et réponses HTTP / JSON
 */

abstract class BaseController {

    /**
     * Envoie une réponse JSON standardisée
     */
    protected function sendJson(mixed $data, int $statusCode = 200, ?string $message = null): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }

        $response = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status'  => $statusCode,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        $response['data'] = $data;

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Envoie une réponse d'erreur JSON standardisée
     */
    protected function sendError(string $message, int $statusCode = 400, array $errors = []): void {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }

        $response = [
            'success' => false,
            'status'  => $statusCode,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Récupère les données envoyées dans la requête (JSON, POST ou PUT)
     * @throws InvalidArgumentException En cas de JSON malformé
     */
    protected function getRequestData(): array {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false || in_array($method, ['PUT', 'PATCH', 'DELETE'], true)) {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                $decoded = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new InvalidArgumentException("Le corps de la requête contient un format JSON invalide : " . json_last_error_msg());
                }
                if (is_array($decoded)) {
                    return array_merge($_GET, $decoded);
                }
            }
        }

        return array_merge($_GET, $_POST);
    }

    /**
     * Gestionnaire centralisé des exceptions
     */
    protected function handleException(Throwable $e): void {
        if ($e instanceof InvalidArgumentException) {
            $this->sendError($e->getMessage(), 400);
        }

        if ($e instanceof RuntimeException) {
            // Vérifier si c'est une ressource introuvable
            if (stripos($e->getMessage(), 'introuvable') !== false) {
                $this->sendError($e->getMessage(), 404);
            }
            $this->sendError($e->getMessage(), 400);
        }

        // Erreur inattendue du serveur
        error_log(sprintf("[SERVER ERROR] %s in %s on line %d", $e->getMessage(), $e->getFile(), $e->getLine()));
        $this->sendError("Une erreur interne est survenue sur le serveur. Veuillez réessayer plus tard.", 500);
    }

    /**
     * Traite les requêtes OPTIONS (CORS preflight)
     */
    public function handleOptions(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            exit;
        }
    }
}

