<?php
/**
 * FreelanceDAO — Data Access Object pour l'entité Freelance
 */

require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../classes/Freelance.php';

class FreelanceDAO extends BaseDAO {

    /**
     * Récupère tous les freelances avec recherche optionnelle
     * @return Freelance[]
     */
    public function findAll(string $search = ''): array {
        try {
            if ($search !== '') {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM freelance
                    WHERE nom LIKE :s OR prenom LIKE :s OR email LIKE :s OR telephone LIKE :s
                    ORDER BY id_freelance DESC
                ");
                $stmt->execute([':s' => "%{$search}%"]);
            } else {
                $stmt = $this->pdo->query("SELECT * FROM freelance ORDER BY id_freelance DESC");
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $freelances = [];
            foreach ($rows as $row) {
                $freelances[] = Freelance::fromArray($row);
            }
            return $freelances;
        } catch (PDOException $e) {
            error_log("FreelanceDAO::findAll - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la récupération des freelances.");
        }
    }

    /**
     * Recherche un freelance par son ID
     */
    public function findById(int $id): ?Freelance {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM freelance WHERE id_freelance = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? Freelance::fromArray($row) : null;
        } catch (PDOException $e) {
            error_log("FreelanceDAO::findById - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la recherche du freelance.");
        }
    }

    /**
     * Recherche un freelance par son email (avec exclusion optionnelle d'ID)
     */
    public function findByEmail(string $email, ?int $excludeId = null): ?Freelance {
        try {
            if ($excludeId !== null) {
                $stmt = $this->pdo->prepare("SELECT * FROM freelance WHERE email = ? AND id_freelance != ?");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("SELECT * FROM freelance WHERE email = ?");
                $stmt->execute([$email]);
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? Freelance::fromArray($row) : null;
        } catch (PDOException $e) {
            error_log("FreelanceDAO::findByEmail - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la vérification de l'email.");
        }
    }

    /**
     * Insère un nouveau freelance
     */
    public function create(Freelance $freelance): int {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO freelance
                    (nom, prenom, email, telephone, description, image, facebook, instagram, linkedin, github)
                VALUES
                    (:nom, :prenom, :email, :telephone, :description, :image, :facebook, :instagram, :linkedin, :github)
            ");
            $stmt->execute([
                ':nom'         => $freelance->getNom(),
                ':prenom'      => $freelance->getPrenom(),
                ':email'       => $freelance->getEmail(),
                ':telephone'   => $freelance->getTelephone(),
                ':description' => $freelance->getDescription(),
                ':image'       => $freelance->getImage(),
                ':facebook'    => $freelance->getFacebook(),
                ':instagram'   => $freelance->getInstagram(),
                ':linkedin'    => $freelance->getLinkedin(),
                ':github'      => $freelance->getGithub(),
            ]);

            $id = (int)$this->pdo->lastInsertId();
            $freelance->setIdFreelance($id);
            return $id;
        } catch (PDOException $e) {
            error_log("FreelanceDAO::create - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la création du freelance.");
        }
    }

    /**
     * Met à jour un freelance existant
     */
    public function update(Freelance $freelance): bool {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE freelance SET
                    nom = :nom, prenom = :prenom, email = :email,
                    telephone = :telephone, description = :description,
                    image = :image, facebook = :facebook,
                    instagram = :instagram, linkedin = :linkedin, github = :github
                WHERE id_freelance = :id
            ");
            return $stmt->execute([
                ':nom'         => $freelance->getNom(),
                ':prenom'      => $freelance->getPrenom(),
                ':email'       => $freelance->getEmail(),
                ':telephone'   => $freelance->getTelephone(),
                ':description' => $freelance->getDescription(),
                ':image'       => $freelance->getImage(),
                ':facebook'    => $freelance->getFacebook(),
                ':instagram'   => $freelance->getInstagram(),
                ':linkedin'    => $freelance->getLinkedin(),
                ':github'      => $freelance->getGithub(),
                ':id'          => $freelance->getIdFreelance(),
            ]);
        } catch (PDOException $e) {
            error_log("FreelanceDAO::update - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la mise à jour du freelance.");
        }
    }

    /**
     * Supprime un freelance par son ID
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM freelance WHERE id_freelance = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("FreelanceDAO::delete - Erreur PDO : " . $e->getMessage());
            throw new RuntimeException("Erreur de base de données lors de la suppression du freelance.");
        }
    }

    /**
     * Compte le nombre total de freelances
     */
    public function count(): int {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM freelance")->fetchColumn();
        } catch (PDOException $e) {
            error_log("FreelanceDAO::count - Erreur PDO : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les derniers freelances ajoutés
     * @return Freelance[]
     */
    public function findRecent(int $limit = 5): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM freelance ORDER BY id_freelance DESC LIMIT :lim");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $freelances = [];
            foreach ($rows as $row) {
                $freelances[] = Freelance::fromArray($row);
            }
            return $freelances;
        } catch (PDOException $e) {
            error_log("FreelanceDAO::findRecent - Erreur PDO : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retourne le décompte des services par freelance [id_freelance => nb_services]
     */
    public function getServiceCountsGrouped(): array {
        try {
            $stmt = $this->pdo->query("SELECT id_freelance, COUNT(*) as nb FROM service GROUP BY id_freelance");
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (PDOException $e) {
            error_log("FreelanceDAO::getServiceCountsGrouped - Erreur PDO : " . $e->getMessage());
            return [];
        }
    }
}

