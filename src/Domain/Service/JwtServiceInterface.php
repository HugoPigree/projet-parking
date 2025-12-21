<?php

namespace App\Domain\Service;

/**
 * Interface pour le service JWT
 * Permet l'inversion de dépendance selon Clean Architecture
 */
interface JwtServiceInterface
{
    /**
     * Génère un token JWT pour un utilisateur
     */
    public function generateToken(int $userId, string $email, string $role): string;

    /**
     * Valide et décode un token JWT
     * Retourne le payload si valide, null sinon
     */
    public function validateToken(string $token): ?array;

    /**
     * Récupère l'ID utilisateur depuis un token
     */
    public function getUserIdFromToken(string $token): ?int;

    /**
     * Extrait le token du header Authorization
     */
    public function extractTokenFromHeader(string $authHeader): ?string;
}

