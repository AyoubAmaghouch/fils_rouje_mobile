<?php
/**
 * FreelanceController — Contrôleur API pour la gestion des Freelances
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/FreelanceService.php';

class FreelanceController extends BaseController {
    private FreelanceService $service;

    public function __construct(?FreelanceService $service = null) {
        $this->service = $service ?? new FreelanceService();
    }

    /**
     * Point d'entrée pour traiter la requête courante
     */
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
                    // Vérifier si c'est une émulation de PUT ou DELETE via POST
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
                        $id = (int)($data['id_freelance'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant du freelance est obligatoire pour la modification.", 400);
                    }
                    $this->update($id);
                    break;

                case 'DELETE':
                    if (!$id) {
                        $data = $this->getRequestData();
                        $id = (int)($data['id_freelance'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant du freelance est obligatoire pour la suppression.", 400);
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
        $freelances = $this->service->getAll($search);
        $this->sendJson($freelances, 200, "Liste des freelances récupérée avec succès.");
    }

    public function show(int $id): void {
        $freelance = $this->service->getById($id);
        $this->sendJson($freelance, 200, "Freelance récupéré avec succès.");
    }

    public function create(): void {
        $data = $this->getRequestData();
        $file = $_FILES['image'] ?? null;
        $serviceIds = array_map('intval', $_POST['services'] ?? $data['services'] ?? []);

        $freelance = $this->service->create($data, $file, $serviceIds);
        $this->sendJson($freelance, 201, "Freelance créé avec succès.");
    }

    public function update(int $id): void {
        $data = $this->getRequestData();
        $file = $_FILES['image'] ?? null;
        $supprimerImage = isset($data['supprimer_image']) && ($data['supprimer_image'] === '1' || $data['supprimer_image'] === true);
        $serviceIds = array_map('intval', $_POST['services'] ?? $data['services'] ?? []);
        $hasServicesField = isset($_POST['services']) || isset($data['services']);

        $freelance = $this->service->update($id, $data, $file, $supprimerImage, $serviceIds, $hasServicesField);
        $this->sendJson($freelance, 200, "Freelance mis à jour avec succès.");
    }

    public function delete(int $id): void {
        $this->service->delete($id);
        $this->sendJson(['id_freelance' => $id], 200, "Freelance supprimé avec succès.");
    }
}

