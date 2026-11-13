<?php
/**
 * ServiceController — Contrôleur API pour la gestion des Services / Gigs
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ServiceService.php';

class ServiceController extends BaseController {
    private ServiceService $service;

    public function __construct(?ServiceService $service = null) {
        $this->service = $service ?? new ServiceService();
    }

    public function handleRequest(): void {
        $this->handleOptions();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        try {
            switch ($method) {
                case 'GET':
                    if ($id) {
                        $this->show($id);
                    } else {
                        $this->index();
                    }
                    break;

                case 'POST':
                    $override = $_POST['_method'] ?? $_GET['_method'] ?? null;
                    if ($override === 'PUT' && $id) {
                        $this->update($id);
                    } elseif ($override === 'DELETE' && $id) {
                        $this->delete($id);
                    } else {
                        $this->create();
                    }
                    break;

                case 'PUT':
                case 'PATCH':
                    if (!$id) {
                        $data = $this->getRequestData();
                        $id = (int)($data['id_service'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant du service est obligatoire pour la modification.", 400);
                    }
                    $this->update($id);
                    break;

                case 'DELETE':
                    if (!$id) {
                        $data = $this->getRequestData();
                        $id = (int)($data['id_service'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant du service est obligatoire pour la suppression.", 400);
                    }
                    $this->delete($id);
                    break;

                default:
                    $this->sendError("Méthode HTTP non autorisée.", 405);
            }
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function index(): void {
        $search = trim($_GET['search'] ?? '');
        $services = $this->service->getAll($search);
        $this->sendJson($services, 200, "Liste des services récupérée avec succès.");
    }

    public function show(int $id): void {
        $service = $this->service->getById($id);
        $this->sendJson($service, 200, "Service récupéré avec succès.");
    }

    public function create(): void {
        $data = $this->getRequestData();
        $file = $_FILES['image_service'] ?? null;
        $service = $this->service->create($data, $file);
        $this->sendJson($service, 201, "Service créé avec succès.");
    }

    public function update(int $id): void {
        $data = $this->getRequestData();
        $file = $_FILES['image_service'] ?? null;
        $supprimerImage = isset($data['supprimer_image']) && ($data['supprimer_image'] === '1' || $data['supprimer_image'] === true);

        $service = $this->service->update($id, $data, $file, $supprimerImage);
        $this->sendJson($service, 200, "Service mis à jour avec succès.");
    }

    public function delete(int $id): void {
        $this->service->delete($id);
        $this->sendJson(['id_service' => $id], 200, "Service supprimé avec succès.");
    }
}

