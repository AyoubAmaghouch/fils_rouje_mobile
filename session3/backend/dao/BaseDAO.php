<?php
/**
 * BaseDAO — Classe abstraite de base pour les Data Access Objects
 */

require_once __DIR__ . '/../config/database.php';

abstract class BaseDAO {
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? getDB();
    }

    public function getPDO(): PDO {
        return $this->pdo;
    }
}

