<?php

/**
 * Routes de l'application
 * Système de routing utilisant le container de dépendances
 */

use App\Config\Dependencies;

// Charger le container de dépendances
$deps = Dependencies::getContainer();

return [
    // Routes GET (affichage de formulaires et pages)
    'GET' => [
        '/' => function() {
            require __DIR__ . '/View/home.php';
        },
        '/home' => function() {
            require __DIR__ . '/View/home.php';
        },
        '/login' => function() {
            Dependencies::get('authController')->showLoginForm();
        },
        '/register' => function() {
            Dependencies::get('authController')->showRegisterForm();
        },
        '/parkings/search' => function() {
            // TODO: Implémenter avec ParkingController
            require __DIR__ . '/View/parking_search.php';
        },
        '/parkings/create' => function() {
            // TODO: Implémenter avec ParkingController
            require __DIR__ . '/View/parking_form.php';
        },
        '/reservations' => function() {
            // TODO: Implémenter avec ReservationController
            require __DIR__ . '/View/reservations.php';
        },
        '/reservations/create' => function() {
            // TODO: Implémenter avec ReservationController
            require __DIR__ . '/View/reservation_form.php';
        },
    ],
    
    // Routes POST (traitement des formulaires et API)
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
        '/api/parkings/search' => function() {
            // TODO: Implémenter avec ParkingController
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Not implemented yet']);
        },
        '/api/parkings/create' => function() {
            // TODO: Implémenter avec ParkingController
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Not implemented yet']);
        },
        '/api/reservations/create' => function() {
            // TODO: Implémenter avec ReservationController
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Not implemented yet']);
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
