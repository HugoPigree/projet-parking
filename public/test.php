<?php
/**
 * Fichier de test simple pour vérifier que PHP fonctionne
 */

echo "✅ PHP fonctionne !<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";

// Vérifier l'autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "✅ vendor/autoload.php existe<br>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload chargé avec succès<br>";
} else {
    echo "❌ vendor/autoload.php n'existe pas<br>";
}

// Tester une classe
try {
    $email = new \App\Domain\ValueObject\Email('test@example.com');
    echo "✅ Classes autoloadées correctement<br>";
    echo "Email test: " . $email->getValue() . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

phpinfo();
