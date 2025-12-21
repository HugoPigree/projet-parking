<?php

namespace App\Infrastructure\InMemory;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;

class InMemoryUserRepository implements UserRepositoryInterface
{
    private static array $users = [];
    private static int $nextId = 1;

    public function findById(int $id): ?User
    {
        return self::$users[$id] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach (self::$users as $user) {
            if ($user->getEmail() === $email->getValue()) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): void
    {
        if ($user->getId() === null) {
            $user->assignId(self::$nextId++);
        }
        self::$users[$user->getId()] = $user;
    }

    public function delete(int $id): void
    {
        unset(self::$users[$id]);
    }

    public function findAll(): array
    {
        return array_values(self::$users);
    }

    public function findByRole(UserRole $role): array
    {
        return array_values(
            array_filter(self::$users, fn(User $user) => $user->getRole() === $role)
        );
    }
    
    /**
     * Réinitialise le repository (utile pour les tests)
     */
    public static function reset(): void
    {
        self::$users = [];
        self::$nextId = 1;
    }
}
