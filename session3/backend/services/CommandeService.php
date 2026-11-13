<?php
/**
 * CommandeService — Logique métier relative aux Commandes
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/ClassificationService.php';
require_once __DIR__ . '/../dao/CommandeDAO.php';
require_once __DIR__ . '/../dao/ServiceDAO.php';
require_once __DIR__ . '/../classes/Commande.php';

class CommandeService extends BaseService {
    private CommandeDAO $commandeDAO;
    private ServiceDAO $serviceDAO;

    public const STATUTS_VALIDES = ['en attente', 'confirmée', 'en cours', 'terminée', 'annulée'];

    public function __construct(?CommandeDAO $commandeDAO = null, ?ServiceDAO $serviceDAO = null) {
        $this->commandeDAO = $commandeDAO ?? new CommandeDAO();
        $this->serviceDAO  = $serviceDAO ?? new ServiceDAO();
    }

    /**
     * Récupère toutes les commandes avec métadonnées de statut
     * @return Commande[]
     */
    public function getAll(string $search = '', string $statutFilter = ''): array {
        $commandes = $this->commandeDAO->findAll($search, $statutFilter);
        return ClassificationService::enrichCommandes($commandes);
    }

    /**
     * Recherche une commande par son ID
     * @throws RuntimeException
     */
    public function getById(int $id): Commande {
        $commande = $this->commandeDAO->findById($id);
        if (!$commande) {
            throw new RuntimeException("Commande introuvable avec l'identifiant #{$id}.");
        }
        $enriched = ClassificationService::enrichCommandes([$commande]);
        return $enriched[0];
    }

    /**
     * Crée une commande
     * @throws InvalidArgumentException
     */
    public function create(array $data): Commande {
        $idService = (int)($data['id_service'] ?? 0);
        $statut = trim($data['statut'] ?? 'en attente');
        $prixTotal = isset($data['prix_total']) ? (float)$data['prix_total'] : -1;
        $dateCommande = !empty($data['date_commande']) ? trim($data['date_commande']) : date('Y-m-d');

        if ($idService <= 0 || !$this->serviceDAO->findById($idService)) {
            throw new InvalidArgumentException("Veuillez sélectionner un service valide.");
        }

        if (!in_array($statut, self::STATUTS_VALIDES, true)) {
            throw new InvalidArgumentException("Le statut « {$statut} » n'est pas un statut valide.");
        }

        if ($prixTotal < 0) {
            throw new InvalidArgumentException("Le prix total doit être un montant supérieur ou égal à zéro.");
        }

        $commande = new Commande(null, $dateCommande, $statut, $prixTotal, $idService);
        $newId = $this->commandeDAO->create($commande);
        $commande->setIdCommande($newId);

        ClassificationService::syncDataFromDB();

        return $this->getById($newId);
    }

    /**
     * Met à jour une commande (statut et prix)
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function update(int $id, array $data): Commande {
        $commande = $this->getById($id);

        $statut = trim($data['statut'] ?? $commande->getStatut());
        $prixTotal = isset($data['prix_total']) ? (float)$data['prix_total'] : $commande->getPrixTotal();

        if (!in_array($statut, self::STATUTS_VALIDES, true)) {
            throw new InvalidArgumentException("Veuillez sélectionner un statut valide parmi : " . implode(', ', self::STATUTS_VALIDES));
        }

        if ($prixTotal < 0) {
            throw new InvalidArgumentException("Le prix total doit être un montant valide.");
        }

        $commande->setStatut($statut);
        $commande->setPrixTotal($prixTotal);

        $this->commandeDAO->update($commande);
        ClassificationService::syncDataFromDB();

        return $this->getById($id);
    }

    /**
     * Supprime une commande
     */
    public function delete(int $id): bool {
        $this->getById($id);
        $result = $this->commandeDAO->delete($id);
        ClassificationService::syncDataFromDB();
        return $result;
    }
}

