<?php
/**
 * Modèle Service
 * Représente l'entité Service / Gig offert par un freelance.
 */

class Service implements JsonSerializable {
    private ?int $id_service;
    private string $titre;
    private string $description;
    private float $prix;
    private ?string $image_service;
    private int $id_freelance;
    private int $id_categorie;

    // Métadonnées enrichies & jointures
    private ?string $nom_freelance = null;
    private ?string $prenom_freelance = null;
    private ?string $nom_categorie = null;
    private array $price_tier = [];
    private array $macro_domain = [];

    public function __construct(
        ?int $id_service = null,
        string $titre = '',
        string $description = '',
        float $prix = 0.0,
        ?string $image_service = null,
        int $id_freelance = 0,
        int $id_categorie = 0
    ) {
        $this->id_service = $id_service;
        $this->titre = $titre;
        $this->description = $description;
        $this->prix = $prix;
        $this->image_service = $image_service;
        $this->id_freelance = $id_freelance;
        $this->id_categorie = $id_categorie;
    }

    public static function fromArray(array $data): self {
        $service = new self(
            isset($data['id_service']) ? (int)$data['id_service'] : null,
            trim($data['titre'] ?? ''),
            trim($data['description'] ?? ''),
            isset($data['prix']) ? (float)$data['prix'] : 0.0,
            !empty($data['image_service']) ? trim($data['image_service']) : null,
            isset($data['id_freelance']) ? (int)$data['id_freelance'] : 0,
            isset($data['id_categorie']) ? (int)$data['id_categorie'] : 0
        );

        if (isset($data['nom_freelance'])) $service->setNomFreelance($data['nom_freelance']);
        if (isset($data['prenom_freelance'])) $service->setPrenomFreelance($data['prenom_freelance']);
        if (isset($data['nom_categorie'])) $service->setNomCategorie($data['nom_categorie']);
        if (isset($data['price_tier']) && is_array($data['price_tier'])) $service->setPriceTier($data['price_tier']);
        if (isset($data['macro_domain']) && is_array($data['macro_domain'])) $service->setMacroDomain($data['macro_domain']);

        return $service;
    }

    public function toArray(): array {
        return [
            'id_service'       => $this->id_service,
            'titre'            => $this->titre,
            'description'      => $this->description,
            'prix'             => $this->prix,
            'image_service'    => $this->image_service,
            'id_freelance'     => $this->id_freelance,
            'id_categorie'     => $this->id_categorie,
            'nom_freelance'    => $this->nom_freelance,
            'prenom_freelance' => $this->prenom_freelance,
            'nom_categorie'    => $this->nom_categorie,
            'price_tier'       => $this->price_tier,
            'macro_domain'     => $this->macro_domain,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    // --- Getters & Setters ---

    public function getIdService(): ?int {
        return $this->id_service;
    }

    public function setIdService(?int $id_service): void {
        $this->id_service = $id_service;
    }

    public function getTitre(): string {
        return $this->titre;
    }

    public function setTitre(string $titre): void {
        $this->titre = $titre;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getPrix(): float {
        return $this->prix;
    }

    public function setPrix(float $prix): void {
        $this->prix = $prix;
    }

    public function getImageService(): ?string {
        return $this->image_service;
    }

    public function setImageService(?string $image_service): void {
        $this->image_service = $image_service;
    }

    public function getIdFreelance(): int {
        return $this->id_freelance;
    }

    public function setIdFreelance(int $id_freelance): void {
        $this->id_freelance = $id_freelance;
    }

    public function getIdCategorie(): int {
        return $this->id_categorie;
    }

    public function setIdCategorie(int $id_categorie): void {
        $this->id_categorie = $id_categorie;
    }

    public function getNomFreelance(): ?string {
        return $this->nom_freelance;
    }

    public function setNomFreelance(?string $nom_freelance): void {
        $this->nom_freelance = $nom_freelance;
    }

    public function getPrenomFreelance(): ?string {
        return $this->prenom_freelance;
    }

    public function setPrenomFreelance(?string $prenom_freelance): void {
        $this->prenom_freelance = $prenom_freelance;
    }

    public function getNomCategorie(): ?string {
        return $this->nom_categorie;
    }

    public function setNomCategorie(?string $nom_categorie): void {
        $this->nom_categorie = $nom_categorie;
    }

    public function getPriceTier(): array {
        return $this->price_tier;
    }

    public function setPriceTier(array $price_tier): void {
        $this->price_tier = $price_tier;
    }

    public function getMacroDomain(): array {
        return $this->macro_domain;
    }

    public function setMacroDomain(array $macro_domain): void {
        $this->macro_domain = $macro_domain;
    }
}

