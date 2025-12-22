<?php
namespace App\bootstrap\pdo;
use PDO;
$config = require __DIR__ . '/../config/database.php';

$pdo = new PDO(
    $config['dsn'],
    $config['username'],
    $config['password'],
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

return $pdo;
