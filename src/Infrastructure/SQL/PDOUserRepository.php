<?php

namespace App\Infrastructure\SQL;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;
use PDO;

class PDOUserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email->getValue()]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function save(User $user): void
    {
        if ($user->getId() === null) {
            $this->insert($user);
        } else {
            $this->update($user);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = [];

        while ($row = $stmt->fetch()) {
            $users[] = $this->hydrateUser($row);
        }

        return $users;
    }

    public function findByRole(UserRole $role): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE role = :role ORDER BY created_at DESC"
        );
        $stmt->execute(['role' => $role->value]);
        $users = [];

        while ($row = $stmt->fetch()) {
            $users[] = $this->hydrateUser($row);
        }

        return $users;
    }

    /**
     * Insère un nouvel utilisateur
     */
    private function insert(User $user): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (email, password, role, nom, prenom, created_at) 
             VALUES (:email, :password, :role, :nom, :prenom, :created_at)"
        );

        $stmt->execute([
            'email' => $user->getEmail(),
            'password' => $user->getPasswordHash(),
            'role' => $user->getRoleValue(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $user->assignId((int) $this->pdo->lastInsertId());
    }

    /**
     * Met à jour un utilisateur existant
     */
    private function update(User $user): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users 
             SET email = :email, password = :password, role = :role, 
                 nom = :nom, prenom = :prenom 
             WHERE id = :id"
        );

        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPasswordHash(),
            'role' => $user->getRoleValue(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
        ]);
    }

    /**
     * Hydrate un User à partir d'une ligne de BDD
     */
    private function hydrateUser(array $row): User
    {
        return new User(
            email: new Email($row['email']),
            passwordHash: $row['password'],
            nom: $row['nom'],
            prenom: $row['prenom'],
            role: UserRole::from($row['role']),
            id: (int) $row['id'],
            createdAt: new \DateTimeImmutable($row['created_at'])
        );
    }
}
