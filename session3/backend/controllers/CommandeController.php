<?php
/**
 * CommandeController — Contrôleur API pour la gestion des Commandes
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CommandeService.php';

class CommandeController extends BaseController {
    private CommandeService $service;

    public function __construct(?CommandeService $service = null) {
        $this->service = $service ?? new CommandeService();
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
                        $id = (int)($data['id_commande'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant de la commande est obligatoire pour la modification.", 400);
                    }
                    $this->update($id);
                    break;

                case 'DELETE':
                    if (!$id) {
                        $data = $this->getRequestData();
                        $id = (int)($data['id_commande'] ?? $data['id'] ?? 0);
                    }
                    if (!$id) {
                        $this->sendError("L'identifiant de la commande est obligatoire pour la suppression.", 400);
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
        $statut = trim($_GET['statut'] ?? '');
        $commandes = $this->service->getAll($search, $statut);
        $this->sendJson($commandes, 200, "Liste des commandes récupérée avec succès.");
    }

    public function show(int $id): void {
        $commande = $this->service->getById($id);
        $this->sendJson($commande, 200, "Commande récupérée avec succès.");
    }

    public function create(): void {
        $data = $this->getRequestData();
        $commande = $this->service->create($data);
        $this->sendJson($commande, 201, "Commande créée avec succès.");
    }

    public function update(int $id): void {
        $data = $this->getRequestData();
        $commande = $this->service->update($id, $data);
        $this->sendJson($commande, 200, "Commande mise à jour avec succès.");
    }

    public function delete(int $id): void {
        $this->service->delete($id);
        $this->sendJson(['id_commande' => $id], 200, "Commande supprimée avec succès.");
    }
}

