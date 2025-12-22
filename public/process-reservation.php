<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;
use App\UseCase\Reservation\CreateReservation;
use App\Domain\Repository\ParkingRepositoryInterface;

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
$startTime = isset($_POST['start_time']) ? $_POST['start_time'] : null;
$endTime = isset($_POST['end_time']) ? $_POST['end_time'] : null;
$slotId = isset($_POST['slot_id']) && $_POST['slot_id'] !== '' ? (int)$_POST['slot_id'] : 1; // Default slot 1

// Validation des données
if (!$parkingId || !$startTime || !$endTime) {
    $_SESSION['reservation_error'] = 'Tous les champs sont requis.';
    header('Location: /reserve-parking.php?parking_id=' . $parkingId);
    exit;
}

try {
    // Convertir les dates
    $start = new DateTime($startTime);
    $end = new DateTime($endTime);

    // Vérifier que la date de fin est après la date de début
    if ($end <= $start) {
        $_SESSION['reservation_error'] = 'La date de fin doit être après la date de début.';
        header('Location: /reserve-parking.php?parking_id=' . $parkingId);
        exit;
    }

    // Vérifier que les dates sont dans le futur
    $now = new DateTime();
    if ($start < $now) {
        $_SESSION['reservation_error'] = 'La date de début doit être dans le futur.';
        header('Location: /reserve-parking.php?parking_id=' . $parkingId);
        exit;
    }

    // Récupérer le parking pour vérifier les horaires
    Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
    $parkingRepo = Dependencies::get('parkingRepository');
    $parking = $parkingRepo->findById($parkingId);

    if (!$parking) {
        $_SESSION['reservation_error'] = 'Parking introuvable.';
        header('Location: /search-parking.php');
        exit;
    }

    // Vérifier que le parking est ouvert pendant tout le créneau
    if (!$parking->isOpenAt($start) || !$parking->isOpenAt($end)) {
        $_SESSION['reservation_error'] = 'Le parking n\'est pas ouvert sur ce créneau horaire.';
        header('Location: /reserve-parking.php?parking_id=' . $parkingId);
        exit;
    }

    // Vérifier que le parking a des places disponibles
    if (!$parking->hasAvailableSlots($start, $end)) {
        $_SESSION['reservation_error'] = 'Aucune place disponible sur ce créneau horaire.';
        header('Location: /reserve-parking.php?parking_id=' . $parkingId);
        exit;
    }

    // Calculer le prix (prix par défaut de 2€/heure)
    $pricePerHour = $parking->getPricingRules();

    // Créer la réservation
    $reservationRepo = Dependencies::get('reservationRepository');
    $invoiceRepo = Dependencies::get('invoiceRepository');
    $createReservation = new CreateReservation($reservationRepo, $invoiceRepo);

    $result = $createReservation->execute(
        $user['id'],
        $parkingId,
        $slotId,
        $start,
        $end,
        $pricePerHour
    );

    $reservation = $result['reservation'];
    $invoice = $result['invoice'];

    // Confirmer la réservation
    $reservation->confirm();
    $reservationRepo->save($reservation);

    // Rediriger vers une page de confirmation
    $_SESSION['reservation_success'] = 'Votre réservation a été créée avec succès !';
    $_SESSION['new_reservation_id'] = $reservation->getId();
    $_SESSION['new_invoice_number'] = $invoice->getInvoiceNumber();

    header('Location: /reservation-confirmed.php');
    exit;

} catch (Exception $e) {
    $_SESSION['reservation_error'] = 'Erreur lors de la création de la réservation : ' . $e->getMessage();
    header('Location: /reserve-parking.php?parking_id=' . $parkingId);
    exit;
}
