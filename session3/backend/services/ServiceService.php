<?php
/**
 * ServiceService — Logique métier relative aux Services (Gigs)
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/ClassificationService.php';
require_once __DIR__ . '/../dao/ServiceDAO.php';
require_once __DIR__ . '/../dao/FreelanceDAO.php';
require_once __DIR__ . '/../dao/CategorieDAO.php';
require_once __DIR__ . '/../classes/Service.php';

class ServiceService extends BaseService {
    private ServiceDAO $serviceDAO;
    private FreelanceDAO $freelanceDAO;
    private CategorieDAO $categorieDAO;
    private string $uploadDir;

    public function __construct(
        ?ServiceDAO $serviceDAO = null,
        ?FreelanceDAO $freelanceDAO = null,
        ?CategorieDAO $categorieDAO = null,
        ?string $uploadDir = null
    ) {
        $this->serviceDAO   = $serviceDAO ?? new ServiceDAO();
        $this->freelanceDAO = $freelanceDAO ?? new FreelanceDAO();
        $this->categorieDAO = $categorieDAO ?? new CategorieDAO();
        $this->uploadDir    = $uploadDir ?? __DIR__ . '/../../frontend/assets/uploads/services/';
    }

    /**
     * Récupère tous les services enrichis (gamme de prix, macro-domaine)
     * @return Service[]
     */
    public function getAll(string $search = ''): array {
        $services = $this->serviceDAO->findAll($search);
        return ClassificationService::enrichServices($services);
    }

    /**
     * Recherche un service par son ID
     * @throws RuntimeException
     */
    public function getById(int $id): Service {
        $service = $this->serviceDAO->findById($id);
        if (!$service) {
            throw new RuntimeException("Service introuvable avec l'identifiant #{$id}.");
        }
        $enriched = ClassificationService::enrichServices([$service]);
        return $enriched[0];
    }

    /**
     * Crée un nouveau service
     * @throws InvalidArgumentException
     */
    public function create(array $data, ?array $file = null): Service {
        $titre = $this->requireNonEmpty($data['titre'] ?? '', 'titre', 'Le titre est obligatoire.');
        $idFreelance = (int)($data['id_freelance'] ?? 0);
        $idCategorie = (int)($data['id_categorie'] ?? 0);
        $prix = (float)($data['prix'] ?? 0);
        $description = $this->requireNonEmpty($data['description'] ?? '', 'description', 'La description est obligatoire.');

        if ($idFreelance <= 0 || !$this->freelanceDAO->findById($idFreelance)) {
            throw new InvalidArgumentException("Veuillez sélectionner un freelance valide.");
        }

        if ($idCategorie <= 0 || !$this->categorieDAO->findById($idCategorie)) {
            throw new InvalidArgumentException("Veuillez sélectionner une catégorie valide.");
        }

        $this->validatePositiveNumber($prix, 'prix');

        $imageName = $this->handleImageUpload($file);

        $service = new Service(null, $titre, $description, $prix, $imageName, $idFreelance, $idCategorie);
        $newId = $this->serviceDAO->create($service);
        $service->setIdService($newId);

        ClassificationService::syncDataFromDB();

        return $service;
    }

    /**
     * Met à jour un service existant
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function update(int $id, array $data, ?array $file = null, bool $supprimerImage = false): Service {
        $service = $this->getById($id);

        $titre = $this->requireNonEmpty($data['titre'] ?? '', 'titre', 'Le titre est obligatoire.');
        $idFreelance = (int)($data['id_freelance'] ?? 0);
        $idCategorie = (int)($data['id_categorie'] ?? 0);
        $prix = (float)($data['prix'] ?? 0);
        $description = $this->requireNonEmpty($data['description'] ?? '', 'description', 'La description est obligatoire.');

        if ($idFreelance <= 0 || !$this->freelanceDAO->findById($idFreelance)) {
            throw new InvalidArgumentException("Veuillez sélectionner un freelance valide.");
        }

        if ($idCategorie <= 0 || !$this->categorieDAO->findById($idCategorie)) {
            throw new InvalidArgumentException("Veuillez sélectionner une catégorie valide.");
        }

        $this->validatePositiveNumber($prix, 'prix');

        $imageName = $service->getImageService();
        if ($file !== null && !empty($file['name'])) {
            $newImage = $this->handleImageUpload($file);
            if ($newImage) {
                $this->removeOldImage($service->getImageService());
                $imageName = $newImage;
            }
        } elseif ($supprimerImage) {
            $this->removeOldImage($service->getImageService());
            $imageName = null;
        }

        $service->setTitre($titre);
        $service->setIdFreelance($idFreelance);
        $service->setIdCategorie($idCategorie);
        $service->setPrix($prix);
        $service->setDescription($description);
        $service->setImageService($imageName);

        $this->serviceDAO->update($service);
        ClassificationService::syncDataFromDB();

        return $service;
    }

    /**
     * Supprime un service
     */
    public function delete(int $id): bool {
        $service = $this->getById($id);
        $this->removeOldImage($service->getImageService());

        $result = $this->serviceDAO->delete($id);
        ClassificationService::syncDataFromDB();
        return $result;
    }

    private function handleImageUpload(?array $file): ?string {
        if (!$file || empty($file['name'])) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException("Erreur lors de l'upload de l'image.");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($extension, $allowedExt)) {
            throw new InvalidArgumentException("Extension d'image non autorisée (JPG, PNG, GIF, WEBP).");
        }

        $mime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = @finfo_file($finfo, $file['tmp_name']);
            if ($finfo) finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = @mime_content_type($file['tmp_name']);
        }
        $mime = $mime ?: ($file['type'] ?? '');

        if (!in_array($mime, $allowedMime)) {
            throw new InvalidArgumentException("Type MIME non autorisé.");
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new InvalidArgumentException("L'image ne doit pas dépasser 5 Mo.");
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $imageName = uniqid('service_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $imageName)) {
            throw new RuntimeException("Échec de l'enregistrement de l'image de service.");
        }

        return $imageName;
    }

    private function removeOldImage(?string $filename): void {
        if (!empty($filename)) {
            $path = $this->uploadDir . $filename;
            if (file_exists($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }
}

