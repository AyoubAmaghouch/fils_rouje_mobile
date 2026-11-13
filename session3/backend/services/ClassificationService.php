<?php
/**
 * ClassificationService — Service de Classification JSON & Métier
 * Hérite de BaseService et communique exclusivement avec ClassificationDAO et les DAOs.
 * Ne lit ni n'écrit jamais directement dans les fichiers JSON.
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ClassificationDAO.php';
require_once __DIR__ . '/../dao/FreelanceDAO.php';
require_once __DIR__ . '/../dao/CategorieDAO.php';
require_once __DIR__ . '/../dao/ServiceDAO.php';
require_once __DIR__ . '/../dao/CommandeDAO.php';

class ClassificationService extends BaseService {

    private ClassificationDAO $classificationDAO;
    private static ?ClassificationDAO $staticDAO = null;

    public function __construct(?ClassificationDAO $dao = null) {
        $this->classificationDAO = $dao ?? new ClassificationDAO();
        self::$staticDAO = $this->classificationDAO;
    }

    private static function getDAO(): ClassificationDAO {
        if (self::$staticDAO === null) {
            self::$staticDAO = new ClassificationDAO();
        }
        return self::$staticDAO;
    }

    /**
     * Charge les données de classification via le DAO
     */
    public static function load(): array {
        return self::getDAO()->load();
    }

    /**
     * Récupère la liste des macro-domaines
     */
    public static function getMacroDomains(): array {
        return self::getDAO()->getMacroDomains();
    }

    /**
     * Trouve le macro-domaine correspondant à un nom de catégorie
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
        $statuts = self::getDAO()->getStatutsCommande();
        $key = strtolower(trim($statut));

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
     * Évalue le niveau d'un freelance selon son nombre de services
     */
    public static function getFreelanceLevel(int $nbServices): array {
        $niveaux = self::getDAO()->getNiveauxFreelance();

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
        $gammes = self::getDAO()->getGammesPrix();

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
     * Enrichit une liste de catégories avec leur macro-domaine
     */
    public static function enrichCategories(array $categories): array {
        foreach ($categories as &$c) {
            $nom = is_object($c) && method_exists($c, 'getNom') ? $c->getNom() : ($c['nom'] ?? '');
            $macro = self::getMacroDomainForCategory($nom);
            if (is_object($c) && method_exists($c, 'setClassification')) {
                $c->setClassification($macro);
            } elseif (is_array($c)) {
                $c['classification'] = $macro;
            }
        }
        return $categories;
    }

    /**
     * Enrichit une liste de services avec leur gamme de prix et macro-domaine
     */
    public static function enrichServices(array $services): array {
        foreach ($services as &$s) {
            $prix = is_object($s) && method_exists($s, 'getPrix') ? $s->getPrix() : (float)($s['prix'] ?? 0);
            $catNom = is_object($s) && method_exists($s, 'getNomCategorie') ? ($s->getNomCategorie() ?? '') : ($s['nom_categorie'] ?? '');

            $priceTier = self::getPriceTier($prix);
            $macroDomain = self::getMacroDomainForCategory($catNom);

            if (is_object($s)) {
                if (method_exists($s, 'setPriceTier')) $s->setPriceTier($priceTier);
                if (method_exists($s, 'setMacroDomain')) $s->setMacroDomain($macroDomain);
            } elseif (is_array($s)) {
                $s['price_tier'] = $priceTier;
                $s['macro_domain'] = $macroDomain;
            }
        }
        return $services;
    }

    /**
     * Enrichit une liste de commandes avec les métadonnées de statut
     */
    public static function enrichCommandes(array $commandes): array {
        foreach ($commandes as &$cmd) {
            $statut = is_object($cmd) && method_exists($cmd, 'getStatut') ? $cmd->getStatut() : ($cmd['statut'] ?? '');
            $meta = self::getOrderStatusConfig($statut);

            if (is_object($cmd) && method_exists($cmd, 'setStatusMeta')) {
                $cmd->setStatusMeta($meta);
            } elseif (is_array($cmd)) {
                $cmd['status_meta'] = $meta;
            }
        }
        return $commandes;
    }

    /**
     * Enrichit une liste de freelances avec leur niveau d'expérience
     */
    public static function enrichFreelances(array $freelances, $pdoOrCounts = null): array {
        $counts = [];
        if (is_array($pdoOrCounts)) {
            $counts = $pdoOrCounts;
        } else {
            $freelanceDAO = new FreelanceDAO(is_a($pdoOrCounts, 'PDO') ? $pdoOrCounts : null);
            $counts = $freelanceDAO->getServiceCountsGrouped();
        }

        foreach ($freelances as &$f) {
            $id = is_object($f) && method_exists($f, 'getIdFreelance') ? $f->getIdFreelance() : ($f['id_freelance'] ?? 0);
            $nb = (int)($counts[$id] ?? 0);
            $level = self::getFreelanceLevel($nb);

            if (is_object($f)) {
                if (method_exists($f, 'setNbServices')) $f->setNbServices($nb);
                if (method_exists($f, 'setLevelMeta')) $f->setLevelMeta($level);
            } elseif (is_array($f)) {
                $f['nb_services'] = $nb;
                $f['level_meta'] = $level;
            }
        }
        return $freelances;
    }

    /**
     * Sauvegarde via le DAO
     */
    public static function save(array $data): bool {
        return self::getDAO()->save($data);
    }

    /**
     * Synchronise les données de la base vers le fichier JSON via les DAOs
     */
    public static function syncDataFromDB($pdo = null): bool {
        try {
            $dao = self::getDAO();
            $data = $dao->load();

            $catDAO = new CategorieDAO($pdo);
            $cats = $catDAO->findAll();
            $rawCats = array_map(function($c) {
                return is_object($c) ? $c->toArray() : $c;
            }, $cats);
            $data['categories_data'] = self::enrichCategories($rawCats);

            $freelanceDAO = new FreelanceDAO($pdo);
            $freelances = $freelanceDAO->findAll();
            $rawFreelances = array_map(function($f) {
                return is_object($f) ? $f->toArray() : $f;
            }, $freelances);
            $data['freelances_data'] = self::enrichFreelances($rawFreelances, $freelanceDAO->getServiceCountsGrouped());

            $serviceDAO = new ServiceDAO($pdo);
            $services = $serviceDAO->findAll();
            $rawServices = array_map(function($s) {
                return is_object($s) ? $s->toArray() : $s;
            }, $services);
            $data['services_data'] = self::enrichServices($rawServices);

            $commandeDAO = new CommandeDAO($pdo);
            $commandes = $commandeDAO->findAll();
            $rawCommandes = array_map(function($cmd) {
                return is_object($cmd) ? $cmd->toArray() : $cmd;
            }, $commandes);
            $data['commandes_data'] = self::enrichCommandes($rawCommandes);

            $data['last_updated'] = date('Y-m-d H:i:s');

            return $dao->save($data);
        } catch (Throwable $e) {
            error_log("ClassificationService::syncDataFromDB - Erreur : " . $e->getMessage());
            return false;
        }
    }
}
