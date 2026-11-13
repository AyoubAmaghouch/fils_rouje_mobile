<?php
/**
 * FreelanceService — Logique métier relative aux Freelances
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/ClassificationService.php';
require_once __DIR__ . '/../dao/FreelanceDAO.php';
require_once __DIR__ . '/../dao/ServiceDAO.php';
require_once __DIR__ . '/../classes/Freelance.php';

class FreelanceService extends BaseService {
    private FreelanceDAO $freelanceDAO;
    private ServiceDAO $serviceDAO;
    private string $uploadDir;

    public function __construct(
        ?FreelanceDAO $freelanceDAO = null,
        ?ServiceDAO $serviceDAO = null,
        ?string $uploadDir = null
    ) {
        $this->freelanceDAO = $freelanceDAO ?? new FreelanceDAO();
        $this->serviceDAO   = $serviceDAO ?? new ServiceDAO();
        $this->uploadDir    = $uploadDir ?? __DIR__ . '/../../frontend/assets/uploads/freelances/';
    }

    /**
     * Récupère tous les freelances enrichis de leurs métadonnées
     * @return Freelance[]
     */
    public function getAll(string $search = ''): array {
        $freelances = $this->freelanceDAO->findAll($search);
        $counts = $this->freelanceDAO->getServiceCountsGrouped();
        return ClassificationService::enrichFreelances($freelances, $counts);
    }

    /**
     * Recherche un freelance par son ID
     * @throws RuntimeException Si non trouvé
     */
    public function getById(int $id): Freelance {
        $freelance = $this->freelanceDAO->findById($id);
        if (!$freelance) {
            throw new RuntimeException("Freelance introuvable avec l'identifiant #{$id}.");
        }
        $counts = $this->freelanceDAO->getServiceCountsGrouped();
        $enriched = ClassificationService::enrichFreelances([$freelance], $counts);
        return $enriched[0];
    }

    /**
     * Crée un nouveau freelance
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function create(array $data, ?array $file = null, array $serviceIds = []): Freelance {
        $nom       = $this->requireNonEmpty($data['nom'] ?? '', 'nom', 'Le nom est obligatoire.');
        $prenom    = $this->requireNonEmpty($data['prenom'] ?? '', 'prenom', 'Le prénom est obligatoire.');
        $email     = $this->validateEmail($data['email'] ?? '');
        $telephone = !empty($data['telephone']) ? trim($data['telephone']) : null;
        $description = !empty($data['description']) ? trim($data['description']) : null;

        // Unicité email
        $existing = $this->freelanceDAO->findByEmail($email);
        if ($existing !== null) {
            throw new InvalidArgumentException("Cette adresse email est déjà utilisée.");
        }

        // URLs optionnelles
        $facebook  = $this->validateOptionalUrl($data['facebook'] ?? null, 'Facebook');
        $instagram = $this->validateOptionalUrl($data['instagram'] ?? null, 'Instagram');
        $linkedin  = $this->validateOptionalUrl($data['linkedin'] ?? null, 'LinkedIn');
        $github    = $this->validateOptionalUrl($data['github'] ?? null, 'GitHub');

        // Gestion de l'image
        $imageName = $this->handleImageUpload($file);

        $freelance = new Freelance(
            null, $nom, $prenom, $email, $telephone, $description,
            $imageName, $facebook, $instagram, $linkedin, $github
        );

        $newId = $this->freelanceDAO->create($freelance);
        $freelance->setIdFreelance($newId);

        // Rattachement des services
        if (!empty($serviceIds)) {
            $this->serviceDAO->assignFreelanceToServices($newId, $serviceIds);
        }

        // Synchronisation du JSON de classification
        ClassificationService::syncDataFromDB();

        return $freelance;
    }

    /**
     * Met à jour un freelance existant
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function update(
        int $id,
        array $data,
        ?array $file = null,
        bool $supprimerImage = false,
        array $serviceIds = [],
        bool $updateServices = false
    ): Freelance {
        $freelance = $this->getById($id);

        $nom       = $this->requireNonEmpty($data['nom'] ?? '', 'nom', 'Le nom est obligatoire.');
        $prenom    = $this->requireNonEmpty($data['prenom'] ?? '', 'prenom', 'Le prénom est obligatoire.');
        $email     = $this->validateEmail($data['email'] ?? '');
        $telephone = !empty($data['telephone']) ? trim($data['telephone']) : null;
        $description = !empty($data['description']) ? trim($data['description']) : null;

        // Vérification unicité email
        $existing = $this->freelanceDAO->findByEmail($email, $id);
        if ($existing !== null) {
            throw new InvalidArgumentException("Cette adresse email est déjà utilisée par un autre freelance.");
        }

        $facebook  = $this->validateOptionalUrl($data['facebook'] ?? null, 'Facebook');
        $instagram = $this->validateOptionalUrl($data['instagram'] ?? null, 'Instagram');
        $linkedin  = $this->validateOptionalUrl($data['linkedin'] ?? null, 'LinkedIn');
        $github    = $this->validateOptionalUrl($data['github'] ?? null, 'GitHub');

        // Image
        $imageName = $freelance->getImage();
        if ($file !== null && !empty($file['name'])) {
            $newImage = $this->handleImageUpload($file);
            if ($newImage) {
                $this->removeOldImage($freelance->getImage());
                $imageName = $newImage;
            }
        } elseif ($supprimerImage) {
            $this->removeOldImage($freelance->getImage());
            $imageName = null;
        }

        $freelance->setNom($nom);
        $freelance->setPrenom($prenom);
        $freelance->setEmail($email);
        $freelance->setTelephone($telephone);
        $freelance->setDescription($description);
        $freelance->setImage($imageName);
        $freelance->setFacebook($facebook);
        $freelance->setInstagram($instagram);
        $freelance->setLinkedin($linkedin);
        $freelance->setGithub($github);

        $this->freelanceDAO->update($freelance);

        // Mise à jour des services rattachés si demandé
        if ($updateServices) {
            $assigned = $this->serviceDAO->findByFreelance($id);
            $assignedIds = array_map(fn($s) => $s->getIdService(), $assigned);

            $unselected = array_diff($assignedIds, $serviceIds);
            if (!empty($unselected)) {
                $this->serviceDAO->unassignFreelanceFromServices($unselected);
            }
            if (!empty($serviceIds)) {
                $this->serviceDAO->assignFreelanceToServices($id, $serviceIds);
            }
        }

        ClassificationService::syncDataFromDB();

        return $freelance;
    }

    /**
     * Supprime un freelance et ses fichiers attachés
     */
    public function delete(int $id): bool {
        $freelance = $this->getById($id);
        $this->removeOldImage($freelance->getImage());

        $result = $this->freelanceDAO->delete($id);
        ClassificationService::syncDataFromDB();
        return $result;
    }

    /**
     * Gestion sécurisée de l'upload d'image
     */
    private function handleImageUpload(?array $file): ?string {
        if (!$file || empty($file['name'])) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new InvalidArgumentException("Erreur lors du téléversement de l'image.");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon'];

        if (!in_array($extension, $allowedExt)) {
            throw new InvalidArgumentException("Extension non autorisée. Utilisez JPG, PNG, GIF ou WEBP.");
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
            throw new InvalidArgumentException("Type de fichier non autorisé comme image.");
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new InvalidArgumentException("L'image ne doit pas dépasser 5 Mo.");
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $imageName = uniqid('freelance_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $imageName)) {
            throw new RuntimeException("Échec de l'enregistrement de l'image sur le serveur.");
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

