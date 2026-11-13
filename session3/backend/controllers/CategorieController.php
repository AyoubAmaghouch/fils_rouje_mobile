<?php
/**
 * CategorieController — Contrôleur API pour la gestion des Catégories de Services
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CategorieService.php';

class CategorieController extends BaseController {
    private CategorieService $service;

    public function __construct(?CategorieService $service = null) {
        $this->service = $service ?? new CategorieService();
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
                        $id = (int)($data['id_categorie'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant de la catégorie est obligatoire pour la modification.", 400);
                    }
                    $this->update($id);
                    break;

                case 'DELETE':
                    if (!$id) {
                        $data = $this->getRequestData();
                        $id = (int)($data['id_categorie'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant de la catégorie est obligatoire pour la suppression.", 400);
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
        $categories = $this->service->getAll($search);
        $this->sendJson($categories, 200, "Liste des catégories récupérée avec succès.");
    }

    public function show(int $id): void {
        $categorie = $this->service->getById($id);
        $this->sendJson($categorie, 200, "Catégorie récupérée avec succès.");
    }

    public function create(): void {
        $data = $this->getRequestData();
        $categorie = $this->service->create($data);
        $this->sendJson($categorie, 201, "Catégorie créée avec succès.");
    }

    public function update(int $id): void {
        $data = $this->getRequestData();
        $categorie = $this->service->update($id, $data);
        $this->sendJson($categorie, 200, "Catégorie mise à jour avec succès.");
    }

    public function delete(int $id): void {
        $this->service->delete($id);
        $this->sendJson(['id_categorie' => $id], 200, "Catégorie supprimée avec succès.");
    }
}

