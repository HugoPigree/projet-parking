<?php
namespace App\UseCase\User;

use App\Domain\Repository\UserRepositoryInterface;

/**
 * Cas d'utilisation : connexion utilisateur.
 * 1. Récupère l'utilisateur via email
 * 2. Vérifie le mot de passe
 * 3. Retourne des infos utiles (ou jette une Exception)
 */
class LoginUser {
    public function __construct(
        private UserRepositoryInterface $userRepo
    ) {}

    public function execute(string $email, string $plainPassword): array {
        // TODO: implémenter la vraie logique d'authentification
        // et potentiellement générer un token plus tard
        return [
            'status' => 'TODO',
            'email' => $email,
        ];
    }
}
