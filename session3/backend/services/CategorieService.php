<?php
/**
 * CategorieService — Logique métier relative aux Catégories
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/ClassificationService.php';
require_once __DIR__ . '/../dao/CategorieDAO.php';
require_once __DIR__ . '/../classes/Categorie.php';

class CategorieService extends BaseService {
    private CategorieDAO $categorieDAO;

    public function __construct(?CategorieDAO $dao = null) {
        $this->categorieDAO = $dao ?? new CategorieDAO();
    }

    /**
     * Récupère toutes les catégories enrichies du macro-domaine
     * @return Categorie[]
     */
    public function getAll(string $search = ''): array {
        $categories = $this->categorieDAO->findAll($search);
        return ClassificationService::enrichCategories($categories);
    }

    /**
     * Recherche une catégorie par son ID
     * @throws RuntimeException Si non trouvée
     */
    public function getById(int $id): Categorie {
        $categorie = $this->categorieDAO->findById($id);
        if (!$categorie) {
            throw new RuntimeException("Catégorie introuvable avec l'identifiant #{$id}.");
        }
        $enriched = ClassificationService::enrichCategories([$categorie]);
        return $enriched[0];
    }

    /**
     * Crée une nouvelle catégorie
     * @throws InvalidArgumentException
     */
    public function create(array $data): Categorie {
        $nom = $this->requireNonEmpty($data['nom'] ?? '', 'nom', 'Le nom de la catégorie est obligatoire.');
        $description = !empty($data['description']) ? trim($data['description']) : null;

        $existing = $this->categorieDAO->findByNom($nom);
        if ($existing !== null) {
            throw new InvalidArgumentException("Cette catégorie existe déjà.");
        }

        $categorie = new Categorie(null, $nom, $description);
        $newId = $this->categorieDAO->create($categorie);
        $categorie->setIdCategorie($newId);

        ClassificationService::syncDataFromDB();

        return $categorie;
    }

    /**
     * Met à jour une catégorie
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function update(int $id, array $data): Categorie {
        $categorie = $this->getById($id);

        $nom = $this->requireNonEmpty($data['nom'] ?? '', 'nom', 'Le nom de la catégorie est obligatoire.');
        $description = !empty($data['description']) ? trim($data['description']) : null;

        $existing = $this->categorieDAO->findByNom($nom, $id);
        if ($existing !== null) {
            throw new InvalidArgumentException("Une autre catégorie utilise déjà ce nom.");
        }

        $categorie->setNom($nom);
        $categorie->setDescription($description);

        $this->categorieDAO->update($categorie);
        ClassificationService::syncDataFromDB();

        return $categorie;
    }

    /**
     * Supprime une catégorie
     */
    public function delete(int $id): bool {
        $this->getById($id);
        $result = $this->categorieDAO->delete($id);
        ClassificationService::syncDataFromDB();
        return $result;
    }
}

