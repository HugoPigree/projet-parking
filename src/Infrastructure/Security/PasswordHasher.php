<?php

namespace App\Infrastructure\Security;

use App\Domain\Service\PasswordHasherInterface;

class PasswordHasher implements PasswordHasherInterface
{
    private int $cost;

    public function __construct(int $cost = 12)
    {
        $this->cost = $cost;
    }

    /**
     * Hash un mot de passe avec bcrypt
     */
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $this->cost
        ]);
    }

    /**
     * Vérifie si un mot de passe correspond au hash
     */
    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Vérifie si le hash doit être recalculé (changement d'algorithme ou de cost)
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, [
            'cost' => $this->cost
        ]);
    }
}
