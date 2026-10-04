<?php
/**
 * Configuration de la base de données
 * Connexion PDO sécurisée à MySQL
 */

// Chargement de la configuration centrale (BASE_URL)
require_once __DIR__ . '/config.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'freelance');
define('DB_USER', 'root');       // Modifier si nécessaire
define('DB_PASS', 'Kingfb12');           // Modifier si nécessaire
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:Inter,sans-serif;background:#1e1e2e;color:#f38ba8;padding:2rem;border-radius:12px;margin:2rem auto;max-width:600px;border:1px solid rgba(239,68,68,0.3);">
                    <h2 style="margin-bottom:1rem;">❌ Erreur de connexion MySQL</h2>
                    <p style="color:#e2e8f0;">' . htmlspecialchars($e->getMessage()) . '</p>
                    <small style="color:#64748b;">Vérifiez vos paramètres dans config/database.php<br>
                    Assurez-vous que MySQL est démarré dans XAMPP.</small>
                 </div>');
        }
    }

    return $pdo;
}
