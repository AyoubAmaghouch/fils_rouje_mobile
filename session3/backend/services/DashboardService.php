<?php
/**
 * DashboardService — Agrégation des indicateurs et statistiques du Dashboard
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/ClassificationService.php';
require_once __DIR__ . '/../dao/FreelanceDAO.php';
require_once __DIR__ . '/../dao/CategorieDAO.php';
require_once __DIR__ . '/../dao/ServiceDAO.php';
require_once __DIR__ . '/../dao/CommandeDAO.php';

class DashboardService extends BaseService {
    private FreelanceDAO $freelanceDAO;
    private CategorieDAO $categorieDAO;
    private ServiceDAO $serviceDAO;
    private CommandeDAO $commandeDAO;

    public function __construct(
        ?FreelanceDAO $freelanceDAO = null,
        ?CategorieDAO $categorieDAO = null,
        ?ServiceDAO $serviceDAO = null,
        ?CommandeDAO $commandeDAO = null
    ) {
        $this->freelanceDAO = $freelanceDAO ?? new FreelanceDAO();
        $this->categorieDAO = $categorieDAO ?? new CategorieDAO();
        $this->serviceDAO   = $serviceDAO ?? new ServiceDAO();
        $this->commandeDAO  = $commandeDAO ?? new CommandeDAO();
    }

    /**
     * Retourne les indicateurs globaux pour les widgets de statistiques
     */
    public function getGlobalStats(): array {
        return [
            'freelances' => $this->freelanceDAO->count(),
            'categories' => $this->categorieDAO->count(),
            'services'   => $this->serviceDAO->count(),
            'commandes'  => $this->commandeDAO->count(),
            'ca_total'   => $this->commandeDAO->sumTotalRevenue(),
        ];
    }

    /**
     * Récupère la répartition des commandes par statut
     */
    public function getCommandesByStatus(): array {
        return $this->commandeDAO->countByStatut();
    }

    /**
     * Récupère les derniers freelances inscrits
     */
    public function getRecentFreelances(int $limit = 5): array {
        return $this->freelanceDAO->findRecent($limit);
    }

    /**
     * Récupère les dernières commandes passées
     */
    public function getRecentCommandes(int $limit = 5): array {
        $cmds = $this->commandeDAO->findRecent($limit);
        return ClassificationService::enrichCommandes($cmds);
    }

    /**
     * Récupère le classement des services les plus demandés
     */
    public function getTopServices(int $limit = 5): array {
        return $this->serviceDAO->findTopServices($limit);
    }
}

