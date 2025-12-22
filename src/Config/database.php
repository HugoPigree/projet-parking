<?php

// Load environment variables
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$host = $_ENV['DB_HOST'] ?? 'mysql';
$dbname = $_ENV['DB_NAME'] ?? 'parking_partage';
$username = $_ENV['DB_USER'] ?? 'parking_user';
$password = $_ENV['DB_PASSWORD'] ?? 'parking_password';

return [
    'dsn'      => "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
    'username' => $username,
    'password' => $password,
];
