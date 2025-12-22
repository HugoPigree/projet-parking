<?php
<<<<<<< HEAD
/**
 * Configuration simple de l'application.
 * Vous pouvez étendre ce tableau plus tard (clé JWT, mode debug, etc.).
 */
return [
    'APP_NAME' => 'Parking Partage',
    'STORAGE_MODE' => 'memory', // 'memory', 'mysql', 'file' (pour évolution future)
];
=======

/**
 * Configuration de l'application
 * Charge les variables d'environnement depuis .env ou utilise les valeurs par défaut
 */

// Charger le fichier .env s'il existe
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

return [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'parking_partage',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'default-secret-key-change-in-production',
        'expiration' => (int) ($_ENV['JWT_EXPIRATION'] ?? 3600),
    ],
];

>>>>>>> origin/feat/subscription-session
