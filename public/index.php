<?php

/**
 * Point d'entrée de l'application
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;

// Charger les routes
$routes = require __DIR__ . '/../src/Interface/routes.php';

// Récupérer la méthode HTTP et l'URI
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Normaliser l'URI (enlever le trailing slash sauf pour la racine)
$uri = rtrim($uri, '/') ?: '/';

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


