<?php

namespace App\Config;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use App\Infrastructure\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Service\PasswordHasherInterface;
use App\Domain\Service\JwtServiceInterface;
use App\Domain\Service\PricingService;
use App\Domain\Service\AvailabilityService;
use App\Infrastructure\InMemory\InMemoryUserRepository;
use App\Infrastructure\InMemory\InMemoryParkingRepository;
use App\Infrastructure\InMemory\InMemoryReservationRepository;
use App\Infrastructure\InMemory\InMemorySubscriptionRepository;
use App\Infrastructure\InMemory\InMemoryParkingSessionRepository;
use App\Infrastructure\InMemory\InMemoryInvoiceRepository;
use App\Infrastructure\SQL\PDOUserRepository;
use App\Infrastructure\Repository\PDOParkingRepository;
use App\Infrastructure\Repository\PDOReservationRepository;
use App\Infrastructure\Repository\PDOSubscriptionRepository;
use App\Infrastructure\Repository\PDOParkingSessionRepository;
use App\Infrastructure\Repository\PDOInvoiceRepository;
use App\Infrastructure\Security\PasswordHasher;
use App\Infrastructure\Security\JwtService;
use App\UseCase\User\LoginUser;
use App\UseCase\User\RegisterUser;
use App\UseCase\ParkingSession\EnterParking;
use App\UseCase\ParkingSession\ExitParking;
use App\UseCase\ParkingSession\ListUserParkingSessions;
use App\UseCase\ParkingSession\ListParkingSessionsByParking;
use App\UseCase\Reservation\ListParkingReservations;
use App\UseCase\Subscription\ListParkingSubscriptions;
use App\UseCase\Subscription\CreateSubscription;
use App\UseCase\Parking\GetParkingRevenue;
use App\Interface\Controller\AuthController;
use App\Interface\AuthMiddleware;

class Dependencies
{
    private static ?array $container = null;
    private static string $storageType = 'sql'; // 'memory' (tests) ou 'sql' (production)

    /**
     * Configure le type de stockage
     * 'memory' : InMemory (pour les tests unitaires uniquement)
     * 'sql' : PDO/SQL (pour la production, par défaut)
     */
    public static function setStorageType(string $type): void
    {
        if (!in_array($type, ['memory', 'sql'])) {
            throw new \InvalidArgumentException("Type de stockage invalide: $type. Utilisez 'memory' (tests) ou 'sql' (production)");
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

        // Force la suppression des repositories statiques en réinstanciant
        // Ceci est nécessaire pour que chaque test ait des repositories vides
        self::clearRepositories();
    }

    /**
     * Force la réinitialisation des repositories statiques
     */
    private static function clearRepositories(): void
    {
        // Cette méthode sera appelée par reset() pour vider les static repositories
        // On le fait en réinitialisant simplement le container qui les recrée
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
        $parkingRepository = self::createParkingRepository();
        $reservationRepository = self::createReservationRepository();
        $subscriptionRepository = self::createSubscriptionRepository();
        $sessionRepository = self::createParkingSessionRepository();
        $invoiceRepository = self::createInvoiceRepository();

        // === SERVICES ===
        $passwordHasher = new PasswordHasher();
        $jwtService = new JwtService(
            $config['jwt']['secret'],
            $config['jwt']['expiration']
        );
        $pricingService = new PricingService();
        $availabilityService = new AvailabilityService();

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

        $enterParking = new EnterParking(
            $sessionRepository,
            $userRepository,
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository,
            $availabilityService
        );

        $exitParking = new ExitParking(
            $sessionRepository,
            $userRepository,
            $reservationRepository,
            $parkingRepository,
            $pricingService
        );

        $listUserParkingSessions = new ListUserParkingSessions(
            $sessionRepository,
            $userRepository
        );

        $listParkingSessionsByParking = new ListParkingSessionsByParking(
            $sessionRepository,
            $parkingRepository
        );

        $listParkingReservations = new ListParkingReservations(
            $reservationRepository,
            $parkingRepository
        );

        $listParkingSubscriptions = new ListParkingSubscriptions(
            $subscriptionRepository,
            $parkingRepository
        );

        $createSubscription = new CreateSubscription(
            $subscriptionRepository,
            $userRepository,
            $parkingRepository
        );

        $getParkingRevenue = new GetParkingRevenue(
            $parkingRepository,
            $reservationRepository,
            $subscriptionRepository
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
            'parkingRepository' => $parkingRepository,
            ParkingRepositoryInterface::class => $parkingRepository,
            'reservationRepository' => $reservationRepository,
            ReservationRepositoryInterface::class => $reservationRepository,
            'subscriptionRepository' => $subscriptionRepository,
            SubscriptionRepositoryInterface::class => $subscriptionRepository,
            'sessionRepository' => $sessionRepository,
            ParkingSessionRepositoryInterface::class => $sessionRepository,
            'invoiceRepository' => $invoiceRepository,
            InvoiceRepositoryInterface::class => $invoiceRepository,

            // Services
            'passwordHasher' => $passwordHasher,
            PasswordHasherInterface::class => $passwordHasher,
            PasswordHasher::class => $passwordHasher,
            'jwtService' => $jwtService,
            JwtServiceInterface::class => $jwtService,
            JwtService::class => $jwtService,
            'pricingService' => $pricingService,
            PricingService::class => $pricingService,
            'availabilityService' => $availabilityService,
            AvailabilityService::class => $availabilityService,

            // Use Cases
            'loginUser' => $loginUser,
            LoginUser::class => $loginUser,
            'registerUser' => $registerUser,
            RegisterUser::class => $registerUser,
            'enterParking' => $enterParking,
            EnterParking::class => $enterParking,
            'exitParking' => $exitParking,
            ExitParking::class => $exitParking,
            'listUserParkingSessions' => $listUserParkingSessions,
            ListUserParkingSessions::class => $listUserParkingSessions,
            'listParkingSessionsByParking' => $listParkingSessionsByParking,
            ListParkingSessionsByParking::class => $listParkingSessionsByParking,
            'listParkingReservations' => $listParkingReservations,
            ListParkingReservations::class => $listParkingReservations,
            'listParkingSubscriptions' => $listParkingSubscriptions,
            ListParkingSubscriptions::class => $listParkingSubscriptions,
            'createSubscription' => $createSubscription,
            CreateSubscription::class => $createSubscription,
            'getParkingRevenue' => $getParkingRevenue,
            GetParkingRevenue::class => $getParkingRevenue,

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
    /**
     * Crée le repository utilisateur selon le type de stockage
     * Conforme au plan : InMemory pour les tests, SQL pour la production
     */
    private static function createUserRepository(): UserRepositoryInterface
    {
        return match (self::$storageType) {
            'memory' => new InMemoryUserRepository(), // Pour les tests unitaires
            'sql' => new PDOUserRepository(),         // Pour la production (par défaut)
            default => new PDOUserRepository(),       // SQL par défaut
        };
    }

    private static function createParkingRepository(): ParkingRepositoryInterface
    {
        return match (self::$storageType) {
            'memory' => new InMemoryParkingRepository(),
            'sql' => new PDOParkingRepository(),
            default => new PDOParkingRepository(),
        };
    }

    private static function createReservationRepository(): ReservationRepositoryInterface
    {
        return match (self::$storageType) {
            'memory' => new InMemoryReservationRepository(),
            'sql' => new PDOReservationRepository(),
            default => new PDOReservationRepository(),
        };
    }

    private static function createSubscriptionRepository(): SubscriptionRepositoryInterface
    {
        return match (self::$storageType) {
            'memory' => new InMemorySubscriptionRepository(),
            'sql' => new PDOSubscriptionRepository(),
            default => new PDOSubscriptionRepository(),
        };
    }

    private static function createParkingSessionRepository(): ParkingSessionRepositoryInterface
    {
        return match (self::$storageType) {
            'memory' => new InMemoryParkingSessionRepository(),
            'sql' => new PDOParkingSessionRepository(),
            default => new PDOParkingSessionRepository(),
        };
    }

    private static function createInvoiceRepository(): InvoiceRepositoryInterface
    {
        return match (self::$storageType) {
            'memory' => new InMemoryInvoiceRepository(),
            'sql' => new PDOInvoiceRepository(),
            default => new PDOInvoiceRepository(),
        };
    }
}
