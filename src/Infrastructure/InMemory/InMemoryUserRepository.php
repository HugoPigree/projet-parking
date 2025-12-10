<?php
namespace App\Infrastructure\InMemory;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;

/**
 * Implémentation temporaire en mémoire (tableaux PHP).
 * Sert de stockage fake le temps du dev, sans base SQL.
 */
class InMemoryUserRepository implements UserRepositoryInterface {
    /** @var User[] */
    private array $users = [];

    public function __construct() {
        // TODO: ajouter quelques faux utilisateurs par défaut si besoin
    }

    public function findByEmail(string $email): ?User {
        // TODO
        return null;
    }

    public function findById(string $id): ?User {
        // TODO
        return null;
    }

    public function save(User $user): void {
        // TODO
    }
}
