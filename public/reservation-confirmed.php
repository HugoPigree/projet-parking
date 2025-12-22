<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

// Vérifier qu'il y a une réservation récente
if (!isset($_SESSION['reservation_success'])) {
    header('Location: /user-dashboard.php');
    exit;
}

$user = $_SESSION['user'];
$successMessage = $_SESSION['reservation_success'];
$reservationId = $_SESSION['new_reservation_id'] ?? null;
$invoiceNumber = $_SESSION['new_invoice_number'] ?? null;

// Récupérer les détails de la réservation
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$reservationRepo = Dependencies::get('reservationRepository');
$reservation = null;

if ($reservationId) {
    $reservation = $reservationRepo->findById($reservationId);
}

// Nettoyer les variables de session
unset($_SESSION['reservation_success']);
unset($_SESSION['new_reservation_id']);
unset($_SESSION['new_invoice_number']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation Confirmée - Parking Partagé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 1rem;
            opacity: 0.95;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .success-card {
            background: white;
            padding: 3rem 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        .success-card h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #28a745;
        }

        .success-card p {
            color: #666;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .reservation-details {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .reservation-details h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.75rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #333;
        }

        .detail-value {
            color: #666;
        }

        .status-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media (max-width: 768px) {
            .actions {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>✅ Réservation Confirmée !</h1>
            <p>Votre place de parking a été réservée avec succès</p>
        </div>
    </div>

    <div class="container">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h2><?= htmlspecialchars($successMessage) ?></h2>
            <p>Vous recevrez un email de confirmation avec tous les détails de votre réservation.</p>
        </div>

        <?php if ($reservation): ?>
            <div class="reservation-details">
                <h3>📋 Détails de votre réservation</h3>

                <div class="detail-row">
                    <span class="detail-label">Numéro de réservation</span>
                    <span class="detail-value">#<?= htmlspecialchars($reservation->getId()) ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">UUID</span>
                    <span class="detail-value"><?= htmlspecialchars($reservation->getUuid()) ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Parking</span>
                    <span class="detail-value">Parking #<?= htmlspecialchars($reservation->getParkingId()) ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Place</span>
                    <span class="detail-value">Place #<?= htmlspecialchars($reservation->getSlotId()) ?></span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Début</span>
                    <span class="detail-value">
                        <?= $reservation->getStartTime()->format('d/m/Y à H:i') ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Fin</span>
                    <span class="detail-value">
                        <?= $reservation->getEndTime()->format('d/m/Y à H:i') ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Durée</span>
                    <span class="detail-value">
                        <?php
                        $duration = $reservation->getEndTime()->diff($reservation->getStartTime());
                        echo $duration->format('%h heures %i minutes');
                        ?>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Prix total</span>
                    <span class="detail-value">
                        <strong><?= number_format($reservation->getTotalPrice(), 2) ?> €</strong>
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="status-badge"><?= htmlspecialchars($reservation->getStatus()) ?></span>
                </div>

                <?php if ($invoiceNumber): ?>
                    <div class="detail-row">
                        <span class="detail-label">Numéro de facture</span>
                        <span class="detail-value"><?= htmlspecialchars($invoiceNumber) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="/user-dashboard.php" class="btn">
                🏠 Retour au dashboard
            </a>
            <a href="/search-parking.php" class="btn btn-secondary">
                🔍 Nouvelle recherche
            </a>
            <?php if ($reservation): ?>
                <a href="/download-invoice.php?reservation_id=<?= $reservation->getId() ?>"
                   class="btn"
                   style="background: #28a745;"
                   target="_blank">
                    📄 Télécharger la facture
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
