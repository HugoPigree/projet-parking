<?php

namespace App\Infrastructure\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private const ALGORITHM = 'HS256';

    public function __construct(
        private string $secretKey,
        private int $expirationTime = 3600
    ) {
    }

    /**
     * Génère un token JWT pour un utilisateur
     */
    public function generateToken(int $userId, string $email, string $role): string
    {
        $issuedAt = time();
        
        return JWT::encode([
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $this->expirationTime,
        ], $this->secretKey, self::ALGORITHM);
    }

    /**
     * Valide et décode un token JWT
     * Retourne le payload si valide, null sinon
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key($this->secretKey, self::ALGORITHM)
            );
            return (array) $decoded;
        } catch (\Exception $e) {
            // Token invalide ou expiré
            return null;
        }
    }

    /**
     * Récupère l'ID utilisateur depuis un token
     */
    public function getUserIdFromToken(string $token): ?int
    {
        $payload = $this->validateToken($token);
        return $payload['user_id'] ?? null;
    }

    /**
     * Extrait le token du header Authorization
     */
    public function extractTokenFromHeader(string $authHeader): ?string
    {
        if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
