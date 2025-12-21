<?php

namespace App\Infrastructure\SQL;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    private function __construct()
    {
        // Singleton - constructeur privé
    }

    /**
     * Initialise la configuration de la base de données
     */
    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$instance = null;
    }

    /**
     * Retourne l'instance PDO (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    /**
     * Crée une nouvelle connexion PDO
     */
    private static function createConnection(): PDO
    {
        // Charger la config si pas déjà fait
        if (empty(self::$config)) {
            $envConfig = require __DIR__ . '/../../Config/env.php';
            self::$config = $envConfig['database'];
        }

        $host = self::$config['host'] ?? 'localhost';
        $dbname = self::$config['name'] ?? 'parking_partage';
        $user = self::$config['user'] ?? 'root';
        $password = self::$config['password'] ?? '';

        try {
            $pdo = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $pdo;
        } catch (PDOException $e) {
            throw new \RuntimeException(
                "Erreur de connexion à la base de données: " . $e->getMessage()
            );
        }
    }

    /**
     * Ferme la connexion
     */
    public static function close(): void
    {
        self::$instance = null;
    }

    /**
     * Vérifie si la connexion est active
     */
    public static function isConnected(): bool
    {
        return self::$instance !== null;
    }
}

