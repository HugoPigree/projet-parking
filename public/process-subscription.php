<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /search-parking.php');
    exit;
}

// Récupérer les données du formulaire
$parkingId = isset($_POST['parking_id']) ? (int)$_POST['parking_id'] : null;
$monthsDuration = isset($_POST['months_duration']) ? (int)$_POST['months_duration'] : null;
$schedule = isset($_POST['schedule']) ? $_POST['schedule'] : [];

// Validation des données
if (!$parkingId || !$monthsDuration) {
    $_SESSION['subscription_error'] = 'Tous les champs sont requis.';
    header('Location: /subscribe-parking.php?parking_id=' . $parkingId);
    exit;
}

// Vérifier la durée (1-12 mois)
if ($monthsDuration < 1 || $monthsDuration > 12) {
    $_SESSION['subscription_error'] = 'La durée doit être entre 1 et 12 mois.';
    header('Location: /subscribe-parking.php?parking_id=' . $parkingId);
    exit;
}

// Vérifier qu'au moins un créneau est sélectionné
if (empty($schedule)) {
    $_SESSION['subscription_error'] = 'Veuillez sélectionner au moins un créneau horaire.';
    header('Location: /subscribe-parking.php?parking_id=' . $parkingId);
    exit;
}

try {
    // Transformer le schedule au format attendu par l'entité
    // Format attendu: ['0' => [['start' => 'HH:MM', 'end' => 'HH:MM']], '1' => [...], ...]
    $weeklySchedule = [];

    foreach ($schedule as $day => $timeSlot) {
        if (isset($timeSlot['start']) && isset($timeSlot['end'])) {
            $weeklySchedule[$day] = [
                [
                    'start' => $timeSlot['start'],
                    'end' => $timeSlot['end']
                ]
            ];
        }
    }

    if (empty($weeklySchedule)) {
        $_SESSION['subscription_error'] = 'Aucun créneau horaire valide sélectionné.';
        header('Location: /subscribe-parking.php?parking_id=' . $parkingId);
        exit;
    }

    // Créer l'abonnement
    Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
    $createSubscription = Dependencies::get('createSubscription');

    $subscription = $createSubscription->execute(
        (string)$user['id'],
        (string)$parkingId,
        $weeklySchedule,
        $monthsDuration
    );

    // Rediriger vers une page de confirmation
    $_SESSION['subscription_success'] = 'Votre abonnement a été créé avec succès !';
    $_SESSION['new_subscription_id'] = $subscription->getId();

    header('Location: /subscription-confirmed.php');
    exit;

} catch (Exception $e) {
    $_SESSION['subscription_error'] = 'Erreur lors de la création de l\'abonnement : ' . $e->getMessage();
    header('Location: /subscribe-parking.php?parking_id=' . $parkingId);
    exit;
}
