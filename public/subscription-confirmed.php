<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

// Vérifier qu'il y a un abonnement récent
if (!isset($_SESSION['subscription_success'])) {
    header('Location: /user-dashboard.php');
    exit;
}

$user = $_SESSION['user'];
$successMessage = $_SESSION['subscription_success'];
$subscriptionId = $_SESSION['new_subscription_id'] ?? null;

// Récupérer les détails de l'abonnement
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$subscriptionRepo = Dependencies::get('subscriptionRepository');
$parkingRepo = Dependencies::get('parkingRepository');
$subscription = null;
$parking = null;

if ($subscriptionId) {
    $subscription = $subscriptionRepo->findById($subscriptionId);
    if ($subscription) {
        $parking = $parkingRepo->findById($subscription->getParkingId());
    }
}

// Nettoyer les variables de session
unset($_SESSION['subscription_success']);
unset($_SESSION['new_subscription_id']);

$daysOfWeek = [
    0 => 'Dimanche',
    1 => 'Lundi',
    2 => 'Mardi',
    3 => 'Mercredi',
    4 => 'Jeudi',
    5 => 'Vendredi',
    6 => 'Samedi'
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement Confirmé - Parking Partagé</title>
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

        .details-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .details-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.75rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .schedule-list {
            list-style: none;
            padding: 0;
        }

        .schedule-item {
            padding: 0.75rem;
            background: #f9f9f9;
            margin-bottom: 0.5rem;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>✅ Abonnement Confirmé !</h1>
            <p>Votre abonnement au parking a été créé avec succès</p>
        </div>
    </div>

    <div class="container">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h2><?= htmlspecialchars($successMessage) ?></h2>
            <p style="color: #666; margin-top: 1rem;">
                Vous pouvez maintenant accéder au parking selon vos créneaux horaires définis.
            </p>
        </div>

        <?php if ($subscription && $parking): ?>
            <div class="details-card">
                <h3>📋 Détails de votre abonnement</h3>

                <div class="detail-row">
                    <span><strong>Parking:</strong></span>
                    <span><?= htmlspecialchars($parking->getName()) ?></span>
                </div>

                <div class="detail-row">
                    <span><strong>Adresse:</strong></span>
                    <span><?= htmlspecialchars($parking->getAddress()) ?></span>
                </div>

                <div class="detail-row">
                    <span><strong>Durée:</strong></span>
                    <span><?= $subscription->getMonthsDuration() ?> mois</span>
                </div>

                <div class="detail-row">
                    <span><strong>Date de début:</strong></span>
                    <span><?= $subscription->getStartDate()->format('d/m/Y') ?></span>
                </div>

                <div class="detail-row">
                    <span><strong>Date de fin:</strong></span>
                    <span><?= $subscription->getEndDate()->format('d/m/Y') ?></span>
                </div>
            </div>

            <div class="details-card">
                <h3>📅 Vos créneaux horaires</h3>

                <ul class="schedule-list">
                    <?php
                    $schedule = $subscription->getWeeklySchedule();
                    foreach ($schedule as $day => $slots):
                        if (!empty($slots)):
                    ?>
                        <li class="schedule-item">
                            <span><strong><?= $daysOfWeek[$day] ?></strong></span>
                            <span>
                                <?php foreach ($slots as $slot): ?>
                                    <?= $slot['start'] ?> - <?= $slot['end'] ?>
                                <?php endforeach; ?>
                            </span>
                        </li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="/user-dashboard.php" class="btn">
                🏠 Retour au dashboard
            </a>
            <a href="/search-parking.php" class="btn btn-secondary">
                🔍 Nouvelle recherche
            </a>
        </div>
    </div>
</body>
</html>
