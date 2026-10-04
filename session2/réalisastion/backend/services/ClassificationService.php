<?php
/**
 * Service de Classification JSON — Freelance Platform
 * Fichier : backend/services/ClassificationService.php
 *
 * Ce service lit et traite les règles et classifications définies dans backend/data/classification.json.
 * Il enrichit les données MySQL sans altérer le schéma ni le stockage de la base de données.
 */

class ClassificationService {

    private static ?array $data = null;
    private static string $jsonPath = __DIR__ . '/../data/classification.json';

    /**
     * Charge et décode le fichier JSON de classification
     */
    public static function load(): array {
        if (self::$data !== null) {
            return self::$data;
        }

        if (!file_exists(self::$jsonPath)) {
            self::$data = [];
            return self::$data;
        }

        $content = file_get_contents(self::$jsonPath);
        $decoded = json_decode($content, true);

        self::$data = is_array($decoded) ? $decoded : [];
        return self::$data;
    }

    /**
     * Récupère les macro-domaines
     */
    public static function getMacroDomains(): array {
        $data = self::load();
        return $data['macro_domains'] ?? [];
    }

    /**
     * Trouve le macro-domaine d'une catégorie donnée (par nom)
     */
    public static function getMacroDomainForCategory(string $categoryName): array {
        $domains = self::getMacroDomains();
        foreach ($domains as $key => $domain) {
            if (isset($domain['categories']) && is_array($domain['categories'])) {
                foreach ($domain['categories'] as $cat) {
                    if (strcasecmp(trim($cat), trim($categoryName)) === 0) {
                        $domain['key'] = $key;
                        return $domain;
                    }
                }
            }
        }
        return [
            'key' => 'general',
            'nom' => 'Général & Divers',
            'icon' => '📁',
            'description' => 'Autres domaines d’activité'
        ];
    }

    /**
     * Récupère la métadonnée d'un statut de commande
     */
    public static function getOrderStatusConfig(string $statut): array {
        $data = self::load();
        $key  = strtolower(trim($statut));
        $statuts = $data['statuts_commande'] ?? [];

        if (isset($statuts[$key])) {
            return $statuts[$key];
        }

        return [
            'label' => ucfirst($statut),
            'badge_class' => 'badge-secondary',
            'icon' => '📌',
            'description' => 'Statut standard.',
            'transitions' => []
        ];
    }

    /**
     * Évalue le niveau d'un freelance selon le nombre de services
     */
    public static function getFreelanceLevel(int $nbServices): array {
        $data = self::load();
        $niveaux = $data['niveaux_freelance'] ?? [];

        if ($nbServices >= ($niveaux['senior']['min_services'] ?? 2)) {
            return $niveaux['senior'] ?? ['badge' => '⭐ Senior', 'badge_class' => 'badge-info'];
        } elseif ($nbServices >= ($niveaux['pro']['min_services'] ?? 1)) {
            return $niveaux['pro'] ?? ['badge' => '⚡ Pro', 'badge_class' => 'badge-secondary'];
        }

        return $niveaux['junior'] ?? ['badge' => '🌱 Junior', 'badge_class' => 'badge-warning'];
    }

    /**
     * Classifie la gamme de prix d'un service (Budget, Standard, Premium)
     */
    public static function getPriceTier(float $prix): array {
        $data = self::load();
        $gammes = $data['gammes_prix'] ?? [];

        if (isset($gammes['budget']['max_prix']) && $prix <= $gammes['budget']['max_prix']) {
            return $gammes['budget'];
        } elseif (isset($gammes['standard']['max_prix']) && $prix <= $gammes['standard']['max_prix']) {
            return $gammes['standard'];
        }

        return $gammes['premium'] ?? [
            'label' => 'Standard',
            'badge' => '⭐ Standard',
            'badge_class' => 'badge-info'
        ];
    }

    /**
     * Enrichit une liste de catégories avec leur classification JSON (Macro-domaine)
     */
    public static function enrichCategories(array $categories): array {
        foreach ($categories as &$c) {
            $c['classification'] = self::getMacroDomainForCategory($c['nom'] ?? '');
        }
        return $categories;
    }

    /**
     * Enrichit une liste de services avec la gamme de prix et le macro-domaine JSON
     */
    public static function enrichServices(array $services): array {
        foreach ($services as &$s) {
            $s['price_tier'] = self::getPriceTier((float)($s['prix'] ?? 0));
            $s['macro_domain'] = self::getMacroDomainForCategory($s['nom_categorie'] ?? '');
        }
        return $services;
    }

    /**
     * Enrichit une liste de commandes avec les métadonnées de statut JSON
     */
    public static function enrichCommandes(array $commandes): array {
        foreach ($commandes as &$cmd) {
            $cmd['status_meta'] = self::getOrderStatusConfig($cmd['statut'] ?? '');
        }
        return $commandes;
    }

    /**
     * Enrichit une liste de freelances avec leur niveau d'expérience basé sur le nombre de services et JSON
     */
    public static function enrichFreelances(array $freelances, PDO $pdo): array {
        $stmt = $pdo->query("SELECT id_freelance, COUNT(*) as nb_services FROM service GROUP BY id_freelance");
        $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        foreach ($freelances as &$f) {
            $nbServices = (int)($counts[$f['id_freelance']] ?? 0);
            $f['nb_services'] = $nbServices;
            $f['level_meta'] = self::getFreelanceLevel($nbServices);
        }
        return $freelances;
    }

    /**
     * Sauvegarde la structure de données dans backend/data/classification.json
     */
    public static function save(array $data): bool {
        self::$data = $data;
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents(self::$jsonPath, $jsonContent) !== false;
    }

    /**
     * Synchronise et sauvegarde l'intégralité des données de la base de données et des classifications dans le fichier JSON
     */
    public static function syncDataFromDB(PDO $pdo): bool {
        $data = self::load();

        // 1. Catégories
        $cats = $pdo->query("SELECT * FROM categorie_service ORDER BY id_categorie ASC")->fetchAll(PDO::FETCH_ASSOC);
        $data['categories_data'] = self::enrichCategories($cats);

        // 2. Freelances
        $freelances = $pdo->query("SELECT * FROM freelance ORDER BY id_freelance ASC")->fetchAll(PDO::FETCH_ASSOC);
        $data['freelances_data'] = self::enrichFreelances($freelances, $pdo);

        // 3. Services
        $services = $pdo->query("SELECT s.*, c.nom AS nom_categorie FROM service s LEFT JOIN categorie_service c ON s.id_categorie = c.id_categorie ORDER BY s.id_service ASC")->fetchAll(PDO::FETCH_ASSOC);
        $data['services_data'] = self::enrichServices($services);

        // 4. Commandes
        $commandes = $pdo->query("SELECT c.*, s.titre AS titre_service FROM commande c LEFT JOIN service s ON c.id_service = s.id_service ORDER BY c.id_commande ASC")->fetchAll(PDO::FETCH_ASSOC);
        $data['commandes_data'] = self::enrichCommandes($commandes);

        $data['last_updated'] = date('Y-m-d H:i:s');

        return self::save($data);
    }
}


