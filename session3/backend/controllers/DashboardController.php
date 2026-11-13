<?php
/**
 * DashboardController — Contrôleur API pour les indicateurs du Dashboard
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController extends BaseController {
    private DashboardService $service;

    public function __construct(?DashboardService $service = null) {
        $this->service = $service ?? new DashboardService();
    }

    public function handleRequest(): void {
        $this->handleOptions();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method !== 'GET') {
            $this->sendError("Méthode HTTP non autorisée pour le tableau de bord.", 405);
        }

        try {
            $data = [
                'stats'             => $this->service->getGlobalStats(),
                'recent_freelances' => $this->service->getRecentFreelances(),
                'recent_commandes'  => $this->service->getRecentCommandes(),
                'status_breakdown'  => $this->service->getCommandesByStatus(),
                'top_services'      => $this->service->getTopServices(),
            ];
            $this->sendJson($data, 200, "Statistiques du dashboard récupérées avec succès.");
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }
}

