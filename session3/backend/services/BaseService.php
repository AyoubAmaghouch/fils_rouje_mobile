<?php
/**
 * BaseService — Classe abstraite parente pour tous les services métier
 */

abstract class BaseService {

    /**
     * Valide qu'une chaîne n'est pas vide
     * @throws InvalidArgumentException
     */
    protected function requireNonEmpty(string $value, string $fieldName, string $customMessage = ''): string {
        $trimmed = trim($value);
        if ($trimmed === '') {
            $msg = $customMessage !== '' ? $customMessage : "Le champ « {$fieldName} » est obligatoire.";
            throw new InvalidArgumentException($msg);
        }
        return $trimmed;
    }

    /**
     * Valide une adresse email
     * @throws InvalidArgumentException
     */
    protected function validateEmail(string $email): string {
        $trimmed = trim($email);
        if ($trimmed === '') {
            throw new InvalidArgumentException("L'adresse email est obligatoire.");
        }
        if (!filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("L'adresse email « {$trimmed} » n'est pas valide.");
        }
        return $trimmed;
    }

    /**
     * Valide une URL optionnelle
     * @throws InvalidArgumentException
     */
    protected function validateOptionalUrl(?string $url, string $fieldName): ?string {
        if ($url === null) return null;
        $trimmed = trim($url);
        if ($trimmed === '') return null;
        if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("L'URL fournie pour « {$fieldName} » n'est pas valide.");
        }
        return $trimmed;
    }

    /**
     * Valide un nombre positif
     * @throws InvalidArgumentException
     */
    protected function validatePositiveNumber(float $value, string $fieldName): float {
        if ($value <= 0) {
            throw new InvalidArgumentException("Le champ « {$fieldName} » doit être un nombre strictement positif.");
        }
        return $value;
    }

    /**
     * Journalise une erreur de manière sécurisée sans exposer les détails techniques au client
     */
    protected function logError(string $context, Throwable $e): void {
        error_log(sprintf("[%s] %s dans %s à la ligne %d", $context, $e->getMessage(), $e->getFile(), $e->getLine()));
    }
}

