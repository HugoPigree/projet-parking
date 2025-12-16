<?php

namespace App\Domain\Service;

interface PasswordHasherInterface
{
    /**
     * Hash un mot de passe
     */
    public function hash(string $password): string;

    /**
     * Vérifie si un mot de passe correspond au hash
     */
    public function verify(string $password, string $hash): bool;
}

