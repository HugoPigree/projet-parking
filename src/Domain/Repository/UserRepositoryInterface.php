<?php
<<<<<<< HEAD
namespace App\Domain\Repository;

use App\Domain\Entity\User;

/**
 * Contrat de stockage des utilisateurs.
 * Permet d'isoler la logique métier de tout stockage concret.
 */
interface UserRepositoryInterface {
    public function findByEmail(string $email): ?User;
    public function findById(string $id): ?User;
    public function save(User $user): void;
}
=======

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;

interface UserRepositoryInterface
{
    /**
     * Trouve un utilisateur par son ID
     */
    public function findById(int $id): ?User;

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail(Email $email): ?User;

    /**
     * Sauvegarde un utilisateur (création ou mise à jour)
     */
    public function save(User $user): void;

    /**
     * Supprime un utilisateur par son ID
     */
    public function delete(int $id): void;

    /**
     * Récupère tous les utilisateurs
     */
    public function findAll(): array;

    /**
     * Trouve tous les utilisateurs par rôle
     */
    public function findByRole(UserRole $role): array;
}

>>>>>>> origin/feat/subscription-session
