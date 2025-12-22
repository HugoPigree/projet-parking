<?php
<<<<<<< HEAD
/**
 * Point d'entrée web.
 * - Charge l'autoloader Composer
 * - Charge le routeur Interface/routes.php
 */

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/Interface/routes.php';
$config = require __DIR__ . '/../src/config/database.php';

$pdo = new PDO(
    $config['dsn'],
    $config['username'],
    $config['password'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
=======

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


>>>>>>> origin/feat/subscription-session
