<?php
/**
 * CommandeDAO — Data Access Object pour l'entité Commande
 */

require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../classes/Commande.php';

class CommandeDAO extends BaseDAO {

    /**
     * Récupère toutes les commandes avec filtres et jointures
     * @return Commande[]
     */
    public function findAll(string $search = '', string $statutFilter = ''): array {
        try {
            $sql = "
                SELECT c.*, s.titre AS titre_service, f.nom AS nom_freelance, f.prenom AS prenom_freelance
                FROM commande c
                LEFT JOIN service s ON c.id_service = s.id_service
                LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
                WHERE 1=1
            ";
            $params = [];

            if ($statutFilter !== '') {
                $sql .= " AND c.statut = :statut";
                $params[':statut'] = $statutFilter;
            }

            if ($search !== '') {
                $sql .= " AND (s.titre LIKE :s OR f.nom LIKE :s OR f.prenom LIKE :s OR c.id_commande LIKE :s)";
                $params[':s'] = "%{$search}%";
            }

            $sql .= " ORDER BY c.id_commande DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $commandes = [];
            foreach ($rows as $row) {
                $commandes[] = Commande::fromArray($row);
            }
            return $commandes;
        } catch (PDOException $e) {
            error_log("CommandeDAO::findAll - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la récupération des commandes.");
        }
    }

    /**
     * Recherche une commande par son ID
     */
    public function findById(int $id): ?Commande {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, s.titre AS titre_service, f.nom AS nom_freelance, f.prenom AS prenom_freelance
                FROM commande c
                LEFT JOIN service s ON c.id_service = s.id_service
                LEFT JOIN freelance f ON s.id_freelance = f.id_freelance
                WHERE c.id_commande = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? Commande::fromArray($row) : null;
        } catch (PDOException $e) {
            error_log("CommandeDAO::findById - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la recherche de la commande.");
        }
    }

    /**
     * Insère une nouvelle commande
     */
    public function create(Commande $commande): int {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO commande (date_commande, statut, prix_total, id_service)
                VALUES (:date_commande, :statut, :prix_total, :id_service)
            ");
            $stmt->execute([
                ':date_commande' => $commande->getDateCommande(),
                ':statut'        => $commande->getStatut(),
                ':prix_total'    => $commande->getPrixTotal(),
                ':id_service'    => $commande->getIdService(),
            ]);

            $id = (int)$this->pdo->lastInsertId();
            $commande->setIdCommande($id);
            return $id;
        } catch (PDOException $e) {
            error_log("CommandeDAO::create - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la création de la commande.");
        }
    }

    /**
     * Met à jour une commande (statut et prix)
     */
    public function update(Commande $commande): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE commande SET statut = :statut, prix_total = :prix_total WHERE id_commande = :id
            ");
            return $stmt->execute([
                ':statut'     => $commande->getStatut(),
                ':prix_total' => $commande->getPrixTotal(),
                ':id'         => $commande->getIdCommande(),
            ]);
        } catch (PDOException $e) {
            error_log("CommandeDAO::update - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la mise à jour de la commande.");
        }
    }

    /**
     * Supprime une commande
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM commande WHERE id_commande = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("CommandeDAO::delete - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la suppression de la commande.");
        }
    }

    /**
     * Compte le nombre total de commandes
     */
    public function count(): int {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM commande")->fetchColumn();
        } catch (PDOException $e) {
            error_log("CommandeDAO::count - Erreur PDO : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcule le chiffre d'affaires total
     */
    public function sumTotalRevenue(): float {
        try {
            return (float)$this->pdo->query("SELECT COALESCE(SUM(prix_total), 0) FROM commande")->fetchColumn();
        } catch (PDOException $e) {
            error_log("CommandeDAO::sumTotalRevenue - Erreur PDO : " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Récupère le nombre de commandes par statut
     */
    public function countByStatut(): array {
        try {
            $stmt = $this->pdo->query("SELECT statut, COUNT(*) as total FROM commande GROUP BY statut ORDER BY total DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("CommandeDAO::countByStatut - Erreur PDO : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les dernières commandes
     * @return Commande[]
     */
    public function findRecent(int $limit = 5): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, s.titre AS titre_service
                FROM commande c
                LEFT JOIN service s ON c.id_service = s.id_service
                ORDER BY c.id_commande DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $commandes = [];
            foreach ($rows as $row) {
                $commandes[] = Commande::fromArray($row);
            }
            return $commandes;
        } catch (PDOException $e) {
            error_log("CommandeDAO::findRecent - Erreur PDO : " . $e->getMessage());
            return [];
        }
    }
}

