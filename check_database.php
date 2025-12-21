<?php

/**
 * Script de vérification et configuration de la base de données
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/src/Config/env.php';
$dbConfig = $config['database'];

$host = $dbConfig['host'] ?? 'localhost';
$dbname = $dbConfig['name'] ?? 'parking_partage';
$user = $dbConfig['user'] ?? 'root';
$password = $dbConfig['password'] ?? '';

echo "=== Vérification de la Base de Données ===\n\n";
echo "Configuration:\n";
echo "  Host: $host\n";
echo "  Database: $dbname\n";
echo "  User: $user\n";
echo "  Password: " . (empty($password) ? '(vide)' : '***') . "\n\n";

// Test de connexion
echo "Test de connexion...\n";
try {
    $pdo = new \PDO(
        "mysql:host={$host};charset=utf8mb4",
        $user,
        $password,
        [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_TIMEOUT => 2,
        ]
    );
    
    echo "✓ Connexion MySQL réussie !\n\n";
    
    // Vérifier si la base existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbname}'");
    $dbExists = $stmt->rowCount() > 0;
    
    if ($dbExists) {
        echo "✓ La base de données '$dbname' existe déjà\n";
        
        $pdo->exec("USE `{$dbname}`");
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "✓ Tables existantes: " . implode(', ', $tables) . "\n";
        } else {
            echo "⚠ Aucune table trouvée. Exécutez: php setup_database.php\n";
        }
    } else {
        echo "⚠ La base de données '$dbname' n'existe pas.\n";
        echo "  Exécutez: php setup_database.php pour la créer\n";
    }
    
    echo "\n✅ MySQL est prêt à être utilisé !\n";
    
} catch (\PDOException $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), '2002') !== false || strpos($e->getMessage(), 'refusée') !== false) {
        echo "🔧 SOLUTION:\n";
        echo "MySQL n'est pas démarré ou n'est pas installé.\n\n";
        echo "Options:\n";
        echo "1. Installer XAMPP (recommandé): https://www.apachefriends.org/\n";
        echo "2. Installer MySQL: https://dev.mysql.com/downloads/mysql/\n";
        echo "3. Utiliser temporairement InMemory (pour les tests uniquement)\n\n";
        echo "Consultez GUIDE_INSTALLATION_MYSQL.md pour plus de détails.\n";
    } else {
        echo "Vérifiez vos identifiants dans .env ou src/Config/env.php\n";
    }
    
    exit(1);
}

