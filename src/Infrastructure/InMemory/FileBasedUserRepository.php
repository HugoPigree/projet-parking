<?php

/**
 * Repository InMemory avec persistance dans un fichier JSON
 * 
 * ⚠️ DÉPRÉCIÉ - Ne plus utiliser
 * 
 * Ce repository a été créé temporairement pour résoudre un problème
 * de persistance avec le serveur PHP de développement.
 * 
 * SOLUTION CONFORME : Utiliser PDOUserRepository (SQL) comme prévu dans le plan.
 * 
 * Ce fichier est conservé pour référence mais n'est plus utilisé par défaut.
 * Pour les tests unitaires, utilisez InMemoryUserRepository.
 * Pour la production, utilisez PDOUserRepository (SQL).
 */

namespace App\Infrastructure\InMemory;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;

/**
 * @deprecated Utilisez PDOUserRepository (SQL) pour la production ou InMemoryUserRepository pour les tests
 */
class FileBasedUserRepository implements UserRepositoryInterface
{
    private static array $users = [];
    private static int $nextId = 1;
    private static string $storageFile;

    public function __construct()
    {
        self::$storageFile = __DIR__ . '/../../../storage/users.json';
        $this->loadFromFile();
    }

    public function findById(int $id): ?User
    {
        $this->loadFromFile();
        return self::$users[$id] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        $this->loadFromFile();
        foreach (self::$users as $user) {
            if ($user->getEmail() === $email->getValue()) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): void
    {
        $this->loadFromFile();
        
        if ($user->getId() === null) {
            $user->assignId(self::$nextId++);
        }
        self::$users[$user->getId()] = $user;
        
        $this->saveToFile();
    }

    public function delete(int $id): void
    {
        $this->loadFromFile();
        unset(self::$users[$id]);
        $this->saveToFile();
    }

    public function findAll(): array
    {
        $this->loadFromFile();
        return array_values(self::$users);
    }

    public function findByRole(UserRole $role): array
    {
        $this->loadFromFile();
        return array_values(
            array_filter(self::$users, fn(User $user) => $user->getRole() === $role)
        );
    }

    /**
     * Charge les utilisateurs depuis le fichier
     */
    private function loadFromFile(): void
    {
        if (!file_exists(self::$storageFile)) {
            self::$users = [];
            self::$nextId = 1;
            return;
        }

        $data = json_decode(file_get_contents(self::$storageFile), true);
        
        if ($data === null) {
            self::$users = [];
            self::$nextId = 1;
            return;
        }

        self::$users = [];
        self::$nextId = $data['nextId'] ?? 1;

        foreach ($data['users'] ?? [] as $userData) {
            $user = new User(
                email: new Email($userData['email']),
                passwordHash: $userData['passwordHash'],
                nom: $userData['nom'],
                prenom: $userData['prenom'],
                role: UserRole::from($userData['role']),
                id: $userData['id'],
                createdAt: new \DateTimeImmutable($userData['createdAt'])
            );
            self::$users[$user->getId()] = $user;
        }
    }

    /**
     * Sauvegarde les utilisateurs dans le fichier
     */
    private function saveToFile(): void
    {
        // Créer le dossier si nécessaire
        $dir = dirname(self::$storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = [
            'nextId' => self::$nextId,
            'users' => array_map(function(User $user) {
                return [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'passwordHash' => $user->getPasswordHash(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'role' => $user->getRoleValue(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ];
            }, self::$users),
        ];

        file_put_contents(self::$storageFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Réinitialise le repository (utile pour les tests)
     */
    public static function reset(): void
    {
        self::$users = [];
        self::$nextId = 1;
        if (file_exists(self::$storageFile)) {
            unlink(self::$storageFile);
        }
    }
}
