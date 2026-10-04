<?php
/**
 * Configuration centrale — Détection automatique du BASE_URL & Gestion de Session
 * Ce fichier calcule le chemin de base du projet dynamiquement et démarre la session.
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return strpos($haystack, $needle) === 0;
    }
}

if (!defined('BASE_URL')) {
    $scriptFile = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $projDir    = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $relPath    = ltrim(str_replace($projDir, '', $scriptFile), '/');
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if (!empty($relPath) && !empty($scriptName) && str_ends_with($scriptName, '/' . $relPath)) {
        $base = substr($scriptName, 0, strlen($scriptName) - strlen('/' . $relPath));
        define('BASE_URL', rtrim($base, '/'));
    } else {
        $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
        if ($docRoot !== '' && $projDir !== '' && str_starts_with($projDir, $docRoot)) {
            $base = substr($projDir, strlen($docRoot));
            define('BASE_URL', rtrim($base, '/'));
        } else {
            define('BASE_URL', '');
        }
    }
}

