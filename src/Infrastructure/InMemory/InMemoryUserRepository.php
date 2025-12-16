<?php

namespace App\Infrastructure\InMemory;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;

class InMemoryUserRepository implements UserRepositoryInterface
{
    private array $users = [];
    private int $nextId = 1;

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email->getValue()) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): void
    {
        if ($user->getId() === null) {
            $user->assignId($this->nextId++);
        }
        $this->users[$user->getId()] = $user;
    }

    public function delete(int $id): void
    {
        unset($this->users[$id]);
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function findByRole(UserRole $role): array
    {
        return array_values(
            array_filter($this->users, fn(User $user) => $user->getRole() === $role)
        );
    }
}
