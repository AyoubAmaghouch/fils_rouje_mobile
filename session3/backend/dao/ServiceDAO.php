<?php
/**
 * ServiceDAO — Data Access Object pour l'entité Service (Gigs)
 */

require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../classes/Service.php';

class ServiceDAO extends BaseDAO {

    /**
     * Récupère tous les services avec jointure sur freelance et catégorie
     * @return Service[]
     */
    public function findAll(string $search = ''): array {
        try {
            $sql = "
                SELECT s.*, f.nom AS nom_freelance, f.prenom AS prenom_freelance, c.nom AS nom_categorie
                FROM service s
                LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
                LEFT JOIN categorie_service c ON s.id_categorie = c.id_categorie
            ";

            if ($search !== '') {
                $sql .= "
                    WHERE s.titre LIKE :s OR s.description LIKE :s OR f.nom LIKE :s OR f.prenom LIKE :s OR c.nom LIKE :s
                    ORDER BY s.id_service DESC
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':s' => "%{$search}%"]);
            } else {
                $sql .= " ORDER BY s.id_service DESC";
                $stmt = $this->pdo->query($sql);
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $services = [];
            foreach ($rows as $row) {
                $services[] = Service::fromArray($row);
            }
            return $services;
        } catch (PDOException $e) {
            error_log("ServiceDAO::findAll - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la récupération des services.");
        }
    }

    /**
     * Recherche un service par son ID
     */
    public function findById(int $id): ?Service {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, f.nom AS nom_freelance, f.prenom AS prenom_freelance, c.nom AS nom_categorie
                FROM service s
                LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
                LEFT JOIN categorie_service c ON s.id_categorie = c.id_categorie
                WHERE s.id_service = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? Service::fromArray($row) : null;
        } catch (PDOException $e) {
            error_log("ServiceDAO::findById - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la recherche du service.");
        }
    }

    /**
     * Récupère les services d'un freelance
     * @return Service[]
     */
    public function findByFreelance(int $freelanceId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, c.nom AS nom_categorie
                FROM service s
                LEFT JOIN categorie_service c ON s.id_categorie = c.id_categorie
                WHERE s.id_freelance = ?
                ORDER BY s.titre ASC
            ");
            $stmt->execute([$freelanceId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $services = [];
            foreach ($rows as $row) {
                $services[] = Service::fromArray($row);
            }
            return $services;
        } catch (PDOException $e) {
            error_log("ServiceDAO::findByFreelance - Erreur PDO : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Insère un nouveau service
     */
    public function create(Service $service): int {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO service (titre, description, prix, image_service, id_freelance, id_categorie)
                VALUES (:titre, :description, :prix, :image_service, :id_freelance, :id_categorie)
            ");
            $stmt->execute([
                ':titre'         => $service->getTitre(),
                ':description'   => $service->getDescription(),
                ':prix'          => $service->getPrix(),
                ':image_service' => $service->getImageService(),
                ':id_freelance'  => $service->getIdFreelance(),
                ':id_categorie'  => $service->getIdCategorie(),
            ]);

            $id = (int)$this->pdo->lastInsertId();
            $service->setIdService($id);
            return $id;
        } catch (PDOException $e) {
            error_log("ServiceDAO::create - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de l'ajout du service.");
        }
    }

    /**
     * Met à jour un service
     */
    public function update(Service $service): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE service SET
                    titre = :titre, description = :description, prix = :prix,
                    image_service = :image_service, id_freelance = :id_freelance, id_categorie = :id_categorie
                WHERE id_service = :id
            ");
            return $stmt->execute([
                ':titre'         => $service->getTitre(),
                ':description'   => $service->getDescription(),
                ':prix'          => $service->getPrix(),
                ':image_service' => $service->getImageService(),
                ':id_freelance'  => $service->getIdFreelance(),
                ':id_categorie'  => $service->getIdCategorie(),
                ':id'            => $service->getIdService(),
            ]);
        } catch (PDOException $e) {
            error_log("ServiceDAO::update - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la mise à jour du service.");
        }
    }

    /**
     * Supprime un service
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM service WHERE id_service = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("ServiceDAO::delete - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la suppression du service.");
        }
    }

    /**
     * Compte le nombre total de services
     */
    public function count(): int {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM service")->fetchColumn();
        } catch (PDOException $e) {
            error_log("ServiceDAO::count - Erreur PDO : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les services les plus commandés
     */
    public function findTopServices(int $limit = 5): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.id_service, s.titre, COUNT(c.id_commande) as nb_commandes, COALESCE(SUM(c.prix_total), 0) as revenu
                FROM service s
                LEFT JOIN commande c ON s.id_service = c.id_service
                GROUP BY s.id_service, s.titre
                ORDER BY nb_commandes DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ServiceDAO::findTopServices - Erreur PDO : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Rattache un freelance à une liste de services
     */
    public function assignFreelanceToServices(int $freelanceId, array $serviceIds): bool {
        if (empty($serviceIds)) return true;
        try {
            $in = implode(',', array_map('intval', $serviceIds));
            return $this->pdo->exec("UPDATE service SET id_freelance = {$freelanceId} WHERE id_service IN ({$in})") !== false;
        } catch (PDOException $e) {
            error_log("ServiceDAO::assignFreelanceToServices - Erreur PDO : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Détache un freelance d'une liste de services
     */
    public function unassignFreelanceFromServices(array $serviceIds): bool {
        if (empty($serviceIds)) return true;
        try {
            $in = implode(',', array_map('intval', $serviceIds));
            return $this->pdo->exec("UPDATE service SET id_freelance = 0 WHERE id_service IN ({$in})") !== false;
        } catch (PDOException $e) {
            error_log("ServiceDAO::unassignFreelanceFromServices - Erreur PDO : " . $e->getMessage());
            return false;
        }
    }
}

