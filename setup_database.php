<?php

/**
 * Script de configuration de la base de données
 * Crée la base de données et les tables nécessaires
 */

require_once __DIR__ . '/vendor/autoload.php';

// Charger la configuration
$config = require __DIR__ . '/src/Config/env.php';
$dbConfig = $config['database'];

$host = $dbConfig['host'] ?? 'localhost';
$dbname = $dbConfig['name'] ?? 'parking_partage';
$user = $dbConfig['user'] ?? 'root';
$password = $dbConfig['password'] ?? '';

echo "=== Configuration de la Base de Données ===\n\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "User: $user\n";
echo "Password: " . (empty($password) ? '(vide)' : '***') . "\n\n";

try {
    // Connexion sans spécifier la base de données (pour la créer)
    echo "1. Connexion au serveur MySQL...\n";
    $pdo = new PDO(
        "mysql:host={$host};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
    echo "   ✓ Connecté au serveur MySQL\n\n";

    // Créer la base de données
    echo "2. Création de la base de données '$dbname'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Base de données créée ou déjà existante\n\n";

    // Sélectionner la base de données
    $pdo->exec("USE `{$dbname}`");
    echo "3. Base de données sélectionnée\n\n";

    // Lire le schéma SQL ligne par ligne
    echo "4. Création des tables...\n";
    $schemaFile = __DIR__ . '/src/Infrastructure/SQL/schema.sql';
    
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Fichier schema.sql introuvable: $schemaFile");
    }

    $lines = file($schemaFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $currentStatement = '';
    $tablesCreated = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Ignorer les commentaires et lignes vides
        if (empty($line) || strpos($line, '--') === 0 || strpos($line, '=') === 0) {
            continue;
        }
        
        // Ignorer CREATE DATABASE et USE (déjà fait)
        if (stripos($line, 'CREATE DATABASE') !== false || stripos($line, 'USE ') !== false) {
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // Si la ligne se termine par ';', c'est la fin d'une requête
        if (substr(rtrim($line), -1) === ';') {
            $statement = trim($currentStatement);
            if (!empty($statement)) {
                // Extraire le nom de la table
                $tableName = 'inconnue';
                if (preg_match('/CREATE TABLE.*?IF NOT EXISTS.*?`?(\w+)`?/i', $statement, $matches)) {
                    $tableName = $matches[1];
                } elseif (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    $tableName = $matches[1];
                }
                
                echo "   - Création de la table: $tableName\n";
                
                try {
                    $pdo->exec($statement);
                    $tablesCreated++;
                    echo "     ✓ Table créée\n";
                } catch (\PDOException $e) {
                    // Ignorer les erreurs "table already exists"
                    if (strpos($e->getMessage(), 'already exists') !== false || 
                        strpos($e->getMessage(), 'Duplicate') !== false) {
                        echo "     - Table déjà existante (ignorée)\n";
                    } else {
                        echo "     ⚠ Erreur: " . $e->getMessage() . "\n";
                    }
                }
            }
            $currentStatement = '';
        }
    }
    
    echo "\n   ✓ $tablesCreated table(s) créée(s) ou vérifiée(s)\n\n";

    // Vérifier les tables créées
    echo "5. Vérification des tables...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
        $count = $stmt->fetchColumn();
        echo "   - $table: $count enregistrement(s)\n";
    }
    
    echo "\n=== Configuration terminée avec succès ! ===\n";
    echo "\nVous pouvez maintenant utiliser l'application avec SQL.\n";
    echo "La base de données '$dbname' est prête.\n";

} catch (PDOException $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n\n";
    echo "Vérifiez:\n";
    echo "1. Que MySQL/MariaDB est installé et démarré\n";
    echo "2. Que les identifiants dans .env ou env.php sont corrects\n";
    echo "3. Que l'utilisateur MySQL a les droits de création de base de données\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

