<?php

/**
 * Point d'entrée de l'application
 */

// Activer l'affichage des erreurs en développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier que l'autoload existe
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    die('❌ Erreur: vendor/autoload.php n\'existe pas. Exécutez "composer install"');
}

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// Récupérer la méthode HTTP et l'URI
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Normaliser l'URI (enlever le trailing slash sauf pour la racine)
$uri = rtrim($uri, '/') ?: '/';

// Rediriger la racine vers le dashboard approprié ou login
if ($uri === '/' && $method === 'GET') {
    if (isset($_SESSION['user'])) {
        $role = $_SESSION['user']['role'];
        if ($role === 'OWNER') {
            header('Location: /owner-dashboard.php');
        } else {
            header('Location: /user-dashboard.php');
        }
    } else {
        header('Location: /login.php');
    }
    exit;
}

// Charger les routes
$routes = require __DIR__ . '/../src/Interface/routes.php';

// Trouver la route correspondante
$routeFound = false;

if (isset($routes[$method][$uri])) {
    $routeFound = true;
    $routes[$method][$uri]();
} else {
    // Route non trouvée
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Route non trouvée',
        'path' => $uri,
        'method' => $method,
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
}
