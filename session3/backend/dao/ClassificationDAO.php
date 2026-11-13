<?php
/**
 * ClassificationDAO — Accès aux données du fichier classification.json
 * Gère exclusivement la lecture et l'écriture dans le fichier JSON.
 */

class ClassificationDAO {
    private string $jsonPath;
    private ?array $cache = null;

    public function __construct(?string $jsonPath = null) {
        $this->jsonPath = $jsonPath ?? __DIR__ . '/../data/classification.json';
    }

    /**
     * Charge et décode le fichier JSON
     * @throws RuntimeException En cas d'erreur de lecture ou de JSON invalide
     */
    public function load(bool $forceReload = false): array {
        if ($this->cache !== null && !$forceReload) {
            return $this->cache;
        }

        if (!file_exists($this->jsonPath)) {
            error_log("ClassificationDAO::load - Fichier introuvable : " . $this->jsonPath);
            $this->cache = [];
            return $this->cache;
        }

        if (!is_readable($this->jsonPath)) {
            error_log("ClassificationDAO::load - Fichier non lisible : " . $this->jsonPath);
            throw new RuntimeException("Le fichier de données JSON n'est pas accessible en lecture.");
        }

        $content = file_get_contents($this->jsonPath);
        if ($content === false) {
            error_log("ClassificationDAO::load - Échec de file_get_contents sur : " . $this->jsonPath);
            throw new RuntimeException("Échec lors de la lecture du fichier de classification JSON.");
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ClassificationDAO::load - JSON syntax error: " . json_last_error_msg());
            throw new RuntimeException("Erreur de syntaxe JSON dans le fichier de classification : " . json_last_error_msg());
        }

        $this->cache = is_array($decoded) ? $decoded : [];
        return $this->cache;
    }

    /**
     * Sauvegarde l'intégralité des données dans le fichier JSON
     * @throws RuntimeException En cas d'erreur d'encodage ou d'écriture
     */
    public function save(array $data): bool {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            error_log("ClassificationDAO::save - JSON encode error: " . json_last_error_msg());
            throw new RuntimeException("Échec de l'encodage des données en JSON : " . json_last_error_msg());
        }

        $dir = dirname($this->jsonPath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log("ClassificationDAO::save - Impossible de créer le répertoire : " . $dir);
                throw new RuntimeException("Impossible de créer le répertoire pour le fichier JSON.");
            }
        }

        $result = @file_put_contents($this->jsonPath, $encoded);
        if ($result === false) {
            error_log("ClassificationDAO::save - Échec d'écriture dans : " . $this->jsonPath);
            throw new RuntimeException("Échec de l'écriture dans le fichier de classification JSON. Vérifiez les permissions.");
        }

        $this->cache = $data;
        return true;
    }

    public function getMacroDomains(): array {
        $data = $this->load();
        return $data['macro_domains'] ?? [];
    }

    public function getStatutsCommande(): array {
        $data = $this->load();
        return $data['statuts_commande'] ?? [];
    }

    public function getNiveauxFreelance(): array {
        $data = $this->load();
        return $data['niveaux_freelance'] ?? [];
    }

    public function getGammesPrix(): array {
        $data = $this->load();
        return $data['gammes_prix'] ?? [];
    }
}

