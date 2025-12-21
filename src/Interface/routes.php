<?php

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


