<?php
/**
 * Modèle Freelance
 * Représente l'entité Freelance dans la plateforme.
 */

class Freelance implements JsonSerializable {
    private ?int $id_freelance;
    private string $nom;
    private string $prenom;
    private string $email;
    private ?string $telephone;
    private ?string $description;
    private ?string $image;
    private ?string $facebook;
    private ?string $instagram;
    private ?string $linkedin;
    private ?string $github;

    // Métadonnées enrichies
    private int $nb_services = 0;
    private array $level_meta = [];

    public function __construct(
        ?int $id_freelance = null,
        string $nom = '',
        string $prenom = '',
        string $email = '',
        ?string $telephone = null,
        ?string $description = null,
        ?string $image = null,
        ?string $facebook = null,
        ?string $instagram = null,
        ?string $linkedin = null,
        ?string $github = null
    ) {
        $this->id_freelance = $id_freelance;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->description = $description;
        $this->image = $image;
        $this->facebook = $facebook;
        $this->instagram = $instagram;
        $this->linkedin = $linkedin;
        $this->github = $github;
    }

    public static function fromArray(array $data): self {
        $freelance = new self(
            isset($data['id_freelance']) ? (int)$data['id_freelance'] : null,
            trim($data['nom'] ?? ''),
            trim($data['prenom'] ?? ''),
            trim($data['email'] ?? ''),
            !empty($data['telephone']) ? trim($data['telephone']) : null,
            !empty($data['description']) ? trim($data['description']) : null,
            !empty($data['image']) ? trim($data['image']) : null,
            !empty($data['facebook']) ? trim($data['facebook']) : null,
            !empty($data['instagram']) ? trim($data['instagram']) : null,
            !empty($data['linkedin']) ? trim($data['linkedin']) : null,
            !empty($data['github']) ? trim($data['github']) : null
        );

        if (isset($data['nb_services'])) {
            $freelance->setNbServices((int)$data['nb_services']);
        }
        if (isset($data['level_meta']) && is_array($data['level_meta'])) {
            $freelance->setLevelMeta($data['level_meta']);
        }

        return $freelance;
    }

    public function toArray(): array {
        return [
            'id_freelance' => $this->id_freelance,
            'nom'          => $this->nom,
            'prenom'       => $this->prenom,
            'email'        => $this->email,
            'telephone'    => $this->telephone,
            'description'  => $this->description,
            'image'        => $this->image,
            'facebook'     => $this->facebook,
            'instagram'    => $this->instagram,
            'linkedin'     => $this->linkedin,
            'github'       => $this->github,
            'nb_services'  => $this->nb_services,
            'level_meta'   => $this->level_meta,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    // --- Getters & Setters ---

    public function getIdFreelance(): ?int {
        return $this->id_freelance;
    }

    public function setIdFreelance(?int $id_freelance): void {
        $this->id_freelance = $id_freelance;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function getNomComplet(): string {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getTelephone(): ?string {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): void {
        $this->telephone = $telephone;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getImage(): ?string {
        return $this->image;
    }

    public function setImage(?string $image): void {
        $this->image = $image;
    }

    public function getFacebook(): ?string {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): void {
        $this->facebook = $facebook;
    }

    public function getInstagram(): ?string {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): void {
        $this->instagram = $instagram;
    }

    public function getLinkedin(): ?string {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): void {
        $this->linkedin = $linkedin;
    }

    public function getGithub(): ?string {
        return $this->github;
    }

    public function setGithub(?string $github): void {
        $this->github = $github;
    }

    public function getNbServices(): int {
        return $this->nb_services;
    }

    public function setNbServices(int $nb_services): void {
        $this->nb_services = $nb_services;
    }

    public function getLevelMeta(): array {
        return $this->level_meta;
    }

    public function setLevelMeta(array $level_meta): void {
        $this->level_meta = $level_meta;
    }
}

