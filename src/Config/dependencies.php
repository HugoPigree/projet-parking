<?php

namespace App\Config;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\PasswordHasherInterface;
use App\Infrastructure\InMemory\InMemoryUserRepository;
use App\Infrastructure\SQL\PDOUserRepository;
use App\Infrastructure\Security\PasswordHasher;
use App\Infrastructure\Security\JwtService;
use App\UseCase\User\LoginUser;
use App\UseCase\User\RegisterUser;
use App\Interface\Controller\AuthController;
use App\Interface\AuthMiddleware;

class Dependencies
{
    private static ?array $container = null;
    private static string $storageType = 'memory'; // 'memory' ou 'sql'

    /**
     * Configure le type de stockage
     */
    public static function setStorageType(string $type): void
    {
        if (!in_array($type, ['memory', 'sql'])) {
            throw new \InvalidArgumentException("Type de stockage invalide: $type");
        }
        self::$storageType = $type;
        self::$container = null; // Réinitialiser le container
    }

    /**
     * Récupère le container de dépendances
     */
    public static function getContainer(): array
    {
        if (self::$container === null) {
            self::$container = self::buildContainer();
        }
        return self::$container;
    }

    /**
     * Récupère une dépendance spécifique
     */
    public static function get(string $key): mixed
    {
        $container = self::getContainer();
        
        if (!isset($container[$key])) {
            throw new \RuntimeException("Dépendance non trouvée: $key");
        }
        
        return $container[$key];
    }

    /**
     * Réinitialise le container (utile pour les tests)
     */
    public static function reset(): void
    {
        self::$container = null;
        self::$storageType = 'memory';
    }

    /**
     * Construit le container de dépendances
     */
    private static function buildContainer(): array
    {
        // Charger la configuration
        $config = require __DIR__ . '/env.php';

        // === REPOSITORIES ===
        $userRepository = self::createUserRepository();

        // === SERVICES ===
        $passwordHasher = new PasswordHasher();
        $jwtService = new JwtService(
            $config['jwt']['secret'],
            $config['jwt']['expiration']
        );

        // === USE CASES ===
        $loginUser = new LoginUser(
            $userRepository,
            $passwordHasher,
            $jwtService
        );

        $registerUser = new RegisterUser(
            $userRepository,
            $passwordHasher
        );

        // === CONTROLLERS ===
        $authController = new AuthController(
            $loginUser,
            $registerUser
        );

        // === MIDDLEWARE ===
        $authMiddleware = new AuthMiddleware(
            $jwtService,
            $userRepository
        );

        return [
            // Repositories
            'userRepository' => $userRepository,
            UserRepositoryInterface::class => $userRepository,

            // Services
            'passwordHasher' => $passwordHasher,
            PasswordHasherInterface::class => $passwordHasher,
            PasswordHasher::class => $passwordHasher,
            'jwtService' => $jwtService,
            JwtService::class => $jwtService,

            // Use Cases
            'loginUser' => $loginUser,
            LoginUser::class => $loginUser,
            'registerUser' => $registerUser,
            RegisterUser::class => $registerUser,

            // Controllers
            'authController' => $authController,
            AuthController::class => $authController,

            // Middleware
            'authMiddleware' => $authMiddleware,
            AuthMiddleware::class => $authMiddleware,
        ];
    }

    /**
     * Crée le repository utilisateur selon le type de stockage
     */
    private static function createUserRepository(): UserRepositoryInterface
    {
        return match (self::$storageType) {
            'sql' => new PDOUserRepository(),
            default => new InMemoryUserRepository(),
        };
    }
}
