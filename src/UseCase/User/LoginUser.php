<?php

namespace App\UseCase\User;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\PasswordHasherInterface;
use App\Domain\Service\JwtServiceInterface;
use App\Domain\ValueObject\Email;

class LoginUser
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private JwtServiceInterface $jwtService
    ) {
    }

    /**
     * Exécute le use case de connexion
     * 
     * @return array{token: string, user: User} Token JWT et données utilisateur
     * @throws \InvalidArgumentException Si les identifiants sont invalides
     */
    public function execute(string $email, string $password): array
    {
        // Créer le Value Object Email (validation incluse)
        try {
            $emailVO = new Email($email);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Identifiants incorrects");
        }

        // Rechercher l'utilisateur par email
        $user = $this->userRepository->findByEmail($emailVO);
        
        if ($user === null) {
            // Message générique pour ne pas révéler si l'email existe
            throw new \InvalidArgumentException("Identifiants incorrects");
        }

        // Vérifier le mot de passe via l'entité (le hash n'est pas exposé)
        $isValid = $user->verifyPassword(
            $password,
            fn(string $pwd, string $hash) => $this->passwordHasher->verify($pwd, $hash)
        );

        if (!$isValid) {
            throw new \InvalidArgumentException("Identifiants incorrects");
        }

        // Générer le token JWT
        $token = $this->jwtService->generateToken(
            $user->getId(),
            $user->getEmail(),
            $user->getRoleValue()
        );

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Récupère l'utilisateur connecté à partir du token
     */
    public function getUserFromToken(string $token): ?User
    {
        $userId = $this->jwtService->getUserIdFromToken($token);
        
        if ($userId === null) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }
}
