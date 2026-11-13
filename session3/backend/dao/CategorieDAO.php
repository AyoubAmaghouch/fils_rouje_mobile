<?php
/**
 * CategorieDAO — Data Access Object pour l'entité Categorie
 */

require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../classes/Categorie.php';

class CategorieDAO extends BaseDAO {

    /**
     * Récupère toutes les catégories avec le nombre de services associés
     * @return Categorie[]
     */
    public function findAll(string $search = ''): array {
        try {
            if ($search !== '') {
                $stmt = $this->pdo->prepare("
                    SELECT c.*, COUNT(s.id_service) as nb_services
                    FROM categorie_service c
                    LEFT JOIN service s ON c.id_categorie = s.id_categorie
                    WHERE c.nom LIKE :s OR c.description LIKE :s
                    GROUP BY c.id_categorie
                    ORDER BY c.id_categorie DESC
                ");
                $stmt->execute([':s' => "%{$search}%"]);
            } else {
                $stmt = $this->pdo->query("
                    SELECT c.*, COUNT(s.id_service) as nb_services
                    FROM categorie_service c
                    LEFT JOIN service s ON c.id_categorie = s.id_categorie
                    GROUP BY c.id_categorie
                    ORDER BY c.id_categorie DESC
                ");
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $categories = [];
            foreach ($rows as $row) {
                $categories[] = Categorie::fromArray($row);
            }
            return $categories;
        } catch (PDOException $e) {
            error_log("CategorieDAO::findAll - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la récupération des catégories.");
        }
    }

    /**
     * Recherche une catégorie par son ID
     */
    public function findById(int $id): ?Categorie {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM categorie_service WHERE id_categorie = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? Categorie::fromArray($row) : null;
        } catch (PDOException $e) {
            error_log("CategorieDAO::findById - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la recherche de la catégorie.");
        }
    }

    /**
     * Recherche une catégorie par son nom (avec exclusion optionnelle d'ID)
     */
    public function findByNom(string $nom, ?int $excludeId = null): ?Categorie {
        try {
            if ($excludeId !== null) {
                $stmt = $this->pdo->prepare("SELECT * FROM categorie_service WHERE nom = ? AND id_categorie != ?");
                $stmt->execute([$nom, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT * FROM categorie_service WHERE nom = ?");
                $stmt->execute([$nom]);
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? Categorie::fromArray($row) : null;
        } catch (PDOException $e) {
            error_log("CategorieDAO::findByNom - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la vérification de la catégorie.");
        }
    }

    /**
     * Insère une nouvelle catégorie
     */
    public function create(Categorie $categorie): int {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO categorie_service (nom, description) VALUES (:nom, :description)");
            $stmt->execute([
                ':nom'         => $categorie->getNom(),
                ':description' => $categorie->getDescription() ?: null,
            ]);

            $id = (int)$this->pdo->lastInsertId();
            $categorie->setIdCategorie($id);
            return $id;
        } catch (PDOException $e) {
            error_log("CategorieDAO::create - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de l'ajout de la catégorie.");
        }
    }

    /**
     * Met à jour une catégorie
     */
    public function update(Categorie $categorie): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE categorie_service SET nom = :nom, description = :description WHERE id_categorie = :id");
            return $stmt->execute([
                ':nom'         => $categorie->getNom(),
                ':description' => $categorie->getDescription() ?: null,
                ':id'          => $categorie->getIdCategorie(),
            ]);
        } catch (PDOException $e) {
            error_log("CategorieDAO::update - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la mise à jour de la catégorie.");
        }
    }

    /**
     * Supprime une catégorie
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM categorie_service WHERE id_categorie = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("CategorieDAO::delete - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la suppression de la catégorie.");
        }
    }

    /**
     * Compte le nombre total de catégories
     */
    public function count(): int {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM categorie_service")->fetchColumn();
        } catch (PDOException $e) {
            error_log("CategorieDAO::count - Erreur PDO : " . $e->getMessage());
            return 0;
        }
    }
}

