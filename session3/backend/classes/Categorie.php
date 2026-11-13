<?php
/**
 * Modèle Categorie
 * Représente l'entité Catégorie de service (categorie_service).
 */

class Categorie implements JsonSerializable {
    private ?int $id_categorie;
    private string $nom;
    private ?string $description;

    // Métadonnées enrichies
    private int $nb_services = 0;
    private array $classification = [];

    public function __construct(
        ?int $id_categorie = null,
        string $nom = '',
        ?string $description = null
    ) {
        $this->id_categorie = $id_categorie;
        $this->nom = $nom;
        $this->description = $description;
    }

    public static function fromArray(array $data): self {
        $cat = new self(
            isset($data['id_categorie']) ? (int)$data['id_categorie'] : null,
            trim($data['nom'] ?? ''),
            !empty($data['description']) ? trim($data['description']) : null
        );

        if (isset($data['nb_services'])) {
            $cat->setNbServices((int)$data['nb_services']);
        }
        if (isset($data['classification']) && is_array($data['classification'])) {
            $cat->setClassification($data['classification']);
        }

        return $cat;
    }

    public function toArray(): array {
        return [
            'id_categorie'   => $this->id_categorie,
            'nom'            => $this->nom,
            'description'    => $this->description,
            'nb_services'    => $this->nb_services,
            'classification' => $this->classification,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    // --- Getters & Setters ---

    public function getIdCategorie(): ?int {
        return $this->id_categorie;
    }

    public function setIdCategorie(?int $id_categorie): void {
        $this->id_categorie = $id_categorie;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getNbServices(): int {
        return $this->nb_services;
    }

    public function setNbServices(int $nb_services): void {
        $this->nb_services = $nb_services;
    }

    public function getClassification(): array {
        return $this->classification;
    }

    public function setClassification(array $classification): void {
        $this->classification = $classification;
    }
}

