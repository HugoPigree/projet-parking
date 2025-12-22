<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;
use App\Domain\Repository\ParkingRepositoryInterface;

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

// Récupérer l'ID du parking
$parkingId = isset($_GET['parking_id']) ? (int)$_GET['parking_id'] : null;

if (!$parkingId) {
    header('Location: /search-parking.php');
    exit;
}

// Récupérer le parking
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$parkingRepo = Dependencies::get('parkingRepository');
$parking = $parkingRepo->findById($parkingId);

if (!$parking) {
    header('Location: /search-parking.php');
    exit;
}

// Dates par défaut (maintenant + 1 heure pour le début, + 2 heures pour la fin)
$defaultStart = (new DateTime())->modify('+1 hour')->format('Y-m-d\TH:i');
$defaultEnd = (new DateTime())->modify('+2 hours')->format('Y-m-d\TH:i');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver une place - <?= htmlspecialchars($parking->getName()) ?></title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .parking-info-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .parking-info-card h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .parking-detail {
            display: flex;
            align-items: start;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            color: #666;
        }

        .parking-detail strong {
            min-width: 150px;
            color: #333;
        }

        .reservation-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .reservation-form h2 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.875rem;
        }

        input[type="datetime-local"],
        input[type="number"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
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
            width: 100%;
        }

        .btn:hover {
            background: #5568d3;
        }

        .info-message {
            background: #e3f2fd;
            color: #1976d2;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📅 Réserver une place</h1>
            <a href="/search-parking.php" class="back-btn">← Retour à la recherche</a>
        </div>
    </div>

    <div class="container">
        <!-- Informations du parking -->
        <div class="parking-info-card">
            <h2>🅿️ <?= htmlspecialchars($parking->getName()) ?></h2>

            <div class="parking-detail">
                <strong>📍 Adresse :</strong>
                <span><?= htmlspecialchars($parking->getAddress()) ?></span>
            </div>

            <div class="parking-detail">
                <strong>🗺️ Coordonnées GPS :</strong>
                <span><?= number_format($parking->getLatitude(), 6) ?>, <?= number_format($parking->getLongitude(), 6) ?></span>
            </div>

            <div class="parking-detail">
                <strong>🚗 Places disponibles :</strong>
                <span><?= $parking->getTotalSpots() ?> places</span>
            </div>
        </div>

        <!-- Formulaire de réservation -->
        <div class="reservation-form">
            <h2>Détails de votre réservation</h2>

            <div class="info-message">
                ℹ️ Choisissez votre créneau de stationnement. Vous serez facturé par tranches de 15 minutes.
            </div>

            <?php if (isset($_SESSION['reservation_error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_SESSION['reservation_error']) ?>
                </div>
                <?php unset($_SESSION['reservation_error']); ?>
            <?php endif; ?>

            <form method="POST" action="/process-reservation.php">
                <input type="hidden" name="parking_id" value="<?= $parking->getId() ?>">

                <div class="form-group">
                    <label for="start_time">Date et heure de début</label>
                    <input type="datetime-local" id="start_time" name="start_time"
                           value="<?= $defaultStart ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_time">Date et heure de fin</label>
                    <input type="datetime-local" id="end_time" name="end_time"
                           value="<?= $defaultEnd ?>" required>
                </div>

                <div class="form-group">
                    <label for="slot_id">Numéro de place (optionnel)</label>
                    <input type="number" id="slot_id" name="slot_id"
                           min="1" max="<?= $parking->getTotalSpots() ?>"
                           placeholder="Laissez vide pour attribution automatique">
                </div>

                <button type="submit" class="btn">
                    ✅ Confirmer la réservation
                </button>
            </form>
        </div>
    </div>
</body>
</html>
