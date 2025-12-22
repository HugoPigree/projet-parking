<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;
use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

// Récupérer l'ID de la réservation
$reservationId = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : null;

if (!$reservationId) {
    die('ID de réservation manquant');
}

// Récupérer la réservation
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$reservationRepo = Dependencies::get('reservationRepository');
$parkingRepo = Dependencies::get('parkingRepository');
$invoiceRepo = Dependencies::get('invoiceRepository');

$reservation = $reservationRepo->findById((string)$reservationId);

if (!$reservation) {
    die('Réservation introuvable');
}

// Vérifier que l'utilisateur est propriétaire de cette réservation
if ($reservation->getUserId() !== $user['id']) {
    die('Accès non autorisé');
}

// Récupérer le parking
$parking = $parkingRepo->findById($reservation->getParkingId());

// Récupérer la facture (chercher par reservation_id)
// Note: Il faudrait ajouter une méthode findByReservationId dans le repository
// Pour l'instant on va créer la facture à la volée
$invoiceNumber = 'INV-' . str_pad($reservationId, 6, '0', STR_PAD_LEFT);

// Calculer la durée
$duration = $reservation->getEndTime()->diff($reservation->getStartTime());
$hours = $duration->h + ($duration->days * 24);
$minutes = $duration->i;

// Créer le HTML de la facture
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #667eea;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            color: #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .total-row {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            background: #28a745;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🅿️ PARKING PARTAGÉ</h1>
        <p>Système de gestion de parking intelligent</p>
    </div>

    <div style="margin: 30px 0;">
        <h2 style="color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
            FACTURE N° ' . htmlspecialchars($invoiceNumber) . '
        </h2>
    </div>

    <div class="info-section">
        <h3 style="color: #667eea;">Informations Client</h3>
        <div class="info-row">
            <span class="info-label">Nom:</span>
            <span>' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span>' . htmlspecialchars($user['email']) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de facturation:</span>
            <span>' . date('d/m/Y H:i') . '</span>
        </div>
    </div>

    <div class="info-section">
        <h3 style="color: #667eea;">Détails de la Réservation</h3>
        <div class="info-row">
            <span class="info-label">Réservation ID:</span>
            <span>#' . htmlspecialchars($reservation->getId()) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">UUID:</span>
            <span>' . htmlspecialchars($reservation->getUuid()) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Parking:</span>
            <span>' . htmlspecialchars($parking ? $parking->getName() : 'Inconnu') . '</span>
        </div>
        ' . ($parking ? '<div class="info-row">
            <span class="info-label">Adresse:</span>
            <span>' . htmlspecialchars($parking->getAddress()) . '</span>
        </div>' : '') . '
        <div class="info-row">
            <span class="info-label">Place:</span>
            <span>Place #' . htmlspecialchars($reservation->getSlotId()) . '</span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut:</span>
            <span class="status">' . htmlspecialchars($reservation->getStatus()) . '</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Date/Heure</th>
                <th>Durée</th>
                <th style="text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Stationnement</td>
                <td>
                    Du ' . $reservation->getStartTime()->format('d/m/Y à H:i') . '<br>
                    Au ' . $reservation->getEndTime()->format('d/m/Y à H:i') . '
                </td>
                <td>' . $hours . 'h ' . $minutes . 'min</td>
                <td style="text-align: right;">' . number_format($reservation->getTotalPrice(), 2) . ' €</td>
            </tr>
            ' . ($reservation->getPenalty() > 0 ? '
            <tr>
                <td colspan="3">Pénalité de retard</td>
                <td style="text-align: right; color: #dc3545;">' . number_format($reservation->getPenalty(), 2) . ' €</td>
            </tr>
            ' : '') . '
            <tr class="total-row">
                <td colspan="3">TOTAL TTC</td>
                <td style="text-align: right;">' . number_format($reservation->getTotalPrice() + $reservation->getPenalty(), 2) . ' €</td>
            </tr>
        </tbody>
    </table>

    <div style="margin: 30px 0; padding: 15px; background: #f0f0f0; border-left: 4px solid #667eea;">
        <strong>Informations de paiement:</strong><br>
        Cette facture a été générée automatiquement par le système Parking Partagé.<br>
        Le paiement est effectué lors de la réservation via notre plateforme sécurisée.
    </div>

    <div class="footer">
        <p>Parking Partagé - Système de gestion de parking intelligent</p>
        <p>Document généré automatiquement le ' . date('d/m/Y à H:i:s') . '</p>
        <p>Merci de votre confiance !</p>
    </div>
</body>
</html>
';

// Configurer dompdf
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Télécharger le PDF
$filename = 'Facture_' . $invoiceNumber . '_' . date('Ymd') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
