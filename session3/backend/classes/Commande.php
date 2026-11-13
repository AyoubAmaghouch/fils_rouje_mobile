<?php
/**
 * Modèle Commande
 * Représente l'entité Commande passée pour un service.
 */

class Commande implements JsonSerializable {
    private ?int $id_commande;
    private string $date_commande;
    private string $statut;
    private float $prix_total;
    private int $id_service;

    // Métadonnées enrichies & jointures
    private ?string $titre_service = null;
    private ?string $nom_freelance = null;
    private ?string $prenom_freelance = null;
    private array $status_meta = [];

    public function __construct(
        ?int $id_commande = null,
        string $date_commande = '',
        string $statut = 'en attente',
        float $prix_total = 0.0,
        int $id_service = 0
    ) {
        $this->id_commande = $id_commande;
        $this->date_commande = $date_commande !== '' ? $date_commande : date('Y-m-d');
        $this->statut = $statut;
        $this->prix_total = $prix_total;
        $this->id_service = $id_service;
    }

    public static function fromArray(array $data): self {
        $commande = new self(
            isset($data['id_commande']) ? (int)$data['id_commande'] : null,
            !empty($data['date_commande']) ? trim($data['date_commande']) : date('Y-m-d'),
            trim($data['statut'] ?? 'en attente'),
            isset($data['prix_total']) ? (float)$data['prix_total'] : 0.0,
            isset($data['id_service']) ? (int)$data['id_service'] : 0
        );

        if (isset($data['titre_service'])) $commande->setTitreService($data['titre_service']);
        if (isset($data['nom_freelance'])) $commande->setNomFreelance($data['nom_freelance']);
        if (isset($data['prenom_freelance'])) $commande->setPrenomFreelance($data['prenom_freelance']);
        if (isset($data['status_meta']) && is_array($data['status_meta'])) $commande->setStatusMeta($data['status_meta']);

        return $commande;
    }

    public function toArray(): array {
        return [
            'id_commande'      => $this->id_commande,
            'date_commande'    => $this->date_commande,
            'statut'           => $this->statut,
            'prix_total'       => $this->prix_total,
            'id_service'       => $this->id_service,
            'titre_service'    => $this->titre_service,
            'nom_freelance'    => $this->nom_freelance,
            'prenom_freelance' => $this->prenom_freelance,
            'status_meta'      => $this->status_meta,
        ];
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    // --- Getters & Setters ---

    public function getIdCommande(): ?int {
        return $this->id_commande;
    }

    public function setIdCommande(?int $id_commande): void {
        $this->id_commande = $id_commande;
    }

    public function getDateCommande(): string {
        return $this->date_commande;
    }

    public function setDateCommande(string $date_commande): void {
        $this->date_commande = $date_commande;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }

    public function getPrixTotal(): float {
        return $this->prix_total;
    }

    public function setPrixTotal(float $prix_total): void {
        $this->prix_total = $prix_total;
    }

    public function getIdService(): int {
        return $this->id_service;
    }

    public function setIdService(int $id_service): void {
        $this->id_service = $id_service;
    }

    public function getTitreService(): ?string {
        return $this->titre_service;
    }

    public function setTitreService(?string $titre_service): void {
        $this->titre_service = $titre_service;
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

    public function getStatusMeta(): array {
        return $this->status_meta;
    }

    public function setStatusMeta(array $status_meta): void {
        $this->status_meta = $status_meta;
    }
}

