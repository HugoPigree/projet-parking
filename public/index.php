<?php
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
