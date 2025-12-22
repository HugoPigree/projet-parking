<?php

namespace App\UseCase\User;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\PasswordHasherInterface;
use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;

class RegisterUser
{
    private const MIN_PASSWORD_LENGTH = 8;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * Exécute le use case d'inscription
     * 
     * @throws \InvalidArgumentException Si les données sont invalides
     * @throws \RuntimeException Si l'email existe déjà
     */
    public function execute(
        string $email,
        string $password,
        string $nom,
        string $prenom,
        string $role = 'USER'
    ): User {
        // Validation du mot de passe
        $this->validatePassword($password);

        // Créer le Value Object Email (validation incluse)
        $emailVO = new Email($email);

        // Parser le rôle
        $userRole = $this->parseRole($role);

        // Vérifier unicité de l'email
        if ($this->userRepository->findByEmail($emailVO) !== null) {
            throw new \RuntimeException("Cet email est déjà utilisé");
        }

        // Hasher le mot de passe
        $passwordHash = $this->passwordHasher->hash($password);

        // Créer l'utilisateur (validation du nom/prénom dans le constructeur)
        $user = new User(
            email: $emailVO,
            passwordHash: $passwordHash,
            nom: $nom,
            prenom: $prenom,
            role: $userRole
        );

        // Sauvegarder
        $this->userRepository->save($user);

        return $user;
    }

    private function validatePassword(string $password): void
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new \InvalidArgumentException(
                "Le mot de passe doit contenir au moins " . self::MIN_PASSWORD_LENGTH . " caractères"
            );
        }
    }

    private function parseRole(string $role): UserRole
    {
        try {
            return UserRole::from(strtoupper($role));
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Rôle invalide: $role");
        }
    }
}
