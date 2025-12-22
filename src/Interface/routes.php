<?php
<<<<<<< HEAD
/**
 * Mini routeur MVC très simple basé sur un paramètre GET 'route'.
 * L'objectif est pédagogique, pas production ready.
 *
 * Ici, on est censé instancier les contrôleurs avec leurs dépendances
 * (repositories in-memory + use cases) avant de  router.
 */

use App\Infrastructure\Repository\PDOInvoiceRepository;
use App\Infrastructure\Repository\PDOParkingRepository;
use App\Infrastructure\Repository\PDOReservationRepository;

use App\Domain\Service\PricingService;
use App\Domain\Service\AvailabilityService;
use App\Infrastructure\InMemory\InMemoryUserRepository;
use App\UseCase\User\LoginUser;
use App\UseCase\Parking\SearchAvailableParkings;
use App\UseCase\Reservation\CreateReservation;
use App\UseCase\Reservation\ListUserReservations;

use App\Interface\Controller\HomeController;
use App\Interface\Controller\AuthController;
use App\Interface\Controller\ReservationController;

// --- Dépendances techniques (InMemory) ---
$userRepo = new InMemoryUserRepository();
$parkingRepo = new PDOParkingRepository();
$resRepo = new PDOReservationRepository();
$invoiceRepository= new PDOInvoiceRepository() ;
// --- Services métier ---
$pricingService = new PricingService();
$availabilityService = new AvailabilityService();

// --- Use cases ---
$loginUserUC = new LoginUser($userRepo);
$searchParkingsUC = new SearchAvailableParkings($parkingRepo);
$listReservationsUC = new ListUserReservations($resRepo);
$createReservationUC = new CreateReservation(
    $resRepo,
   $invoiceRepository
);

// --- Contrôleurs MVC ---
$homeController = new HomeController($searchParkingsUC);
$authController = new AuthController($loginUserUC);
//$resController = new ReservationController($listReservationsUC, $createReservationUC,$cancelReservationUc,$getReservationUc,$deleteReservationUc);

// --- Routing ---
$route = $_GET['route'] ?? 'home';

switch ($route) {
    case 'home':
        $homeController->index();
        break;
    case 'login':
        $authController->loginForm();
        break;
    case 'loginSubmit':
        $authController->loginSubmit();
        break;
    case 'reservations':
        // TODO: userId réel (session plus tard )
        $resController->listForUser('USER_ID_TODO');
        break;
    case 'reservationForm':
        $resController->createForm();
        break;
    case 'createReservationSubmit':
        $resController->createSubmit();
        break;
    case 'reservation.show':
    $resController->show($_GET['id']);
    break;

case 'reservation.createForm':
    $resController->createForm();
    break;

case 'reservation.createSubmit':
    $resController->createSubmit();
    break;

case 'reservation.cancel':
    $resController->cancel($_GET['id'], 'USER_ID_TODO');
    break;

case 'reservation.delete':
    $resController->delete($_GET['id']);
    break;

case 'reservations':
    $resController->listForUser('USER_ID_TODO');
    break;

    default:
        http_response_code(404);
        echo "404 Not Found";
}
=======

/**
 * Routes de l'application
 * Système de routing simple pour tester l'authentification
 */

use App\Config\Dependencies;

return [
    // Routes GET (affichage de formulaires)
    'GET' => [
        '/' => function() {
            require __DIR__ . '/View/home.php';
        },
        '/login' => function() {
            Dependencies::get('authController')->showLoginForm();
        },
        '/register' => function() {
            Dependencies::get('authController')->showRegisterForm();
        },
    ],
    
    // Routes POST (traitement des formulaires)
    'POST' => [
        '/api/login' => function() {
            Dependencies::get('authController')->login();
        },
        '/api/register' => function() {
            Dependencies::get('authController')->register();
        },
        '/api/logout' => function() {
            Dependencies::get('authController')->logout();
        },
        '/api/me' => function() {
            Dependencies::get('authController')->getCurrentUser();
        },
        '/api/debug/users' => function() {
            $repo = Dependencies::get('userRepository');
            $users = $repo->findAll();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'count' => count($users),
                'users' => array_map(fn($u) => [
                    'id' => $u->getId(),
                    'email' => $u->getEmail(),
                    'nom' => $u->getNom(),
                    'prenom' => $u->getPrenom(),
                    'role' => $u->getRoleValue(),
                ], $users),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        },
    ],
];


>>>>>>> origin/feat/subscription-session
