<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;

session_start();

// Vérifier que l'utilisateur est connecté et est un propriétaire
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'OWNER') {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
$selectedParkingId = $_GET['parking_id'] ?? null;

// Récupérer les use cases
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$parkingRepo = Dependencies::get('parkingRepository');
$getParkingRevenue = Dependencies::get('getParkingRevenue');
$listParkingReservations = Dependencies::get('listParkingReservations');
$listParkingSessionsByParking = Dependencies::get('listParkingSessionsByParking');
$listParkingSubscriptions = Dependencies::get('listParkingSubscriptions');

// Récupérer les parkings du propriétaire
$parkings = $parkingRepo->findByOwner($user['id']);

// Calculer les statistiques globales
$totalParkings = count($parkings);
$totalSpots = array_sum(array_map(fn($p) => $p->getTotalSpots(), $parkings));

// Calculer le chiffre d'affaire du mois en cours pour tous les parkings
$currentYear = (int)date('Y');
$currentMonth = (int)date('m');
$totalRevenue = 0;
foreach ($parkings as $parking) {
    try {
        $revenueData = $getParkingRevenue->execute($parking->getId(), $user['id'], $currentYear, $currentMonth);
        $totalRevenue += $revenueData['totalRevenue'];
    } catch (\Exception $e) {
        // Ignorer les erreurs pour les parkings individuels
    }
}

// Si un parking est sélectionné, récupérer ses détails
$selectedParking = null;
$parkingReservations = [];
$parkingSessions = [];
$parkingSubscriptions = [];
$parkingRevenue = null;

if ($selectedParkingId) {
    $selectedParking = $parkingRepo->findById((int)$selectedParkingId);

    if ($selectedParking && $selectedParking->getOwnerId() === $user['id']) {
        try {
            $parkingReservations = $listParkingReservations->execute((int)$selectedParkingId, $user['id']);
        } catch (\Exception $e) {
            $parkingReservations = [];
        }

        try {
            $parkingSessions = $listParkingSessionsByParking->execute((string)$selectedParkingId, $user['id']);
        } catch (\Exception $e) {
            $parkingSessions = [];
        }

        try {
            $parkingSubscriptions = $listParkingSubscriptions->execute((string)$selectedParkingId, $user['id']);
        } catch (\Exception $e) {
            $parkingSubscriptions = [];
        }

        try {
            $parkingRevenue = $getParkingRevenue->execute((int)$selectedParkingId, $user['id'], $currentYear, $currentMonth);
        } catch (\Exception $e) {
            $parkingRevenue = null;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Propriétaire - Parking Partagé</title>
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
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }

        .section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .section h2 {
            font-size: 1.25rem;
            color: #333;
        }

        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .parking-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .parking-card {
            flex: 1;
            min-width: 250px;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .parking-card:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .parking-card.active {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .parking-card h3 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .parking-card p {
            font-size: 0.875rem;
            color: #666;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #f8f9fa;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
        }

        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.875rem;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-completed {
            background: #d4edda;
            color: #155724;
        }

        .badge-active {
            background: #cfe2ff;
            color: #084298;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }

        .revenue-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .revenue-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }

        .revenue-item h4 {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .revenue-item .amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }

        .tabs {
            display: flex;
            gap: 0.5rem;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
            background: none;
            border-left: none;
            border-right: none;
            border-top: none;
            font-size: 0.875rem;
            font-weight: 500;
            color: #666;
        }

        .tab:hover {
            color: #667eea;
        }

        .tab.active {
            border-bottom-color: #667eea;
            color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📊 Dashboard Propriétaire</h1>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></span>
                <a href="/logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistiques globales -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Mes Parkings</h3>
                <div class="value"><?= $totalParkings ?></div>
            </div>
            <div class="stat-card">
                <h3>Places Totales</h3>
                <div class="value"><?= $totalSpots ?></div>
            </div>
            <div class="stat-card">
                <h3>Revenus du Mois</h3>
                <div class="value"><?= number_format($totalRevenue, 2) ?> €</div>
            </div>
        </div>

        <!-- Sélecteur de parking -->
        <?php if (!empty($parkings)): ?>
            <div class="section">
                <div class="section-header">
                    <h2>Sélectionnez un parking</h2>
                    <a href="/create-parking.php" class="btn btn-small">+ Nouveau Parking</a>
                </div>
                <div class="parking-selector">
                    <?php foreach ($parkings as $parking): ?>
                        <a href="?parking_id=<?= $parking->getId() ?>"
                           class="parking-card <?= $selectedParkingId == $parking->getId() ? 'active' : '' ?>">
                            <h3>🅿️ <?= htmlspecialchars($parking->getName()) ?></h3>
                            <p>📍 <?= htmlspecialchars($parking->getAddress()) ?></p>
                            <p>🚗 <?= $parking->getTotalSpots() ?> places</p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="section">
                <div class="empty-state">
                    <h2>Aucun parking</h2>
                    <p>Créez votre premier parking pour commencer</p>
                    <br>
                    <a href="/create-parking.php" class="btn">+ Créer un Parking</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Détails du parking sélectionné -->
        <?php if ($selectedParking): ?>

            <!-- Chiffre d'affaire -->
            <div class="section">
                <div class="section-header">
                    <h2>💰 Chiffre d'Affaire - <?= date('F Y') ?></h2>
                </div>
                <?php if ($parkingRevenue): ?>
                    <div class="revenue-breakdown">
                        <div class="revenue-item">
                            <h4>Réservations</h4>
                            <div class="amount"><?= number_format($parkingRevenue['reservationsRevenue'], 2) ?> €</div>
                            <p style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                <?= $parkingRevenue['reservationCount'] ?> réservations
                            </p>
                        </div>
                        <div class="revenue-item">
                            <h4>Abonnements</h4>
                            <div class="amount"><?= number_format($parkingRevenue['subscriptionsRevenue'], 2) ?> €</div>
                            <p style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                <?= $parkingRevenue['subscriptionCount'] ?> abonnements actifs
                            </p>
                        </div>
                        <div class="revenue-item">
                            <h4>Total</h4>
                            <div class="amount"><?= number_format($parkingRevenue['totalRevenue'], 2) ?> €</div>
                            <p style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                <?= $parkingRevenue['startDate'] ?> au <?= $parkingRevenue['endDate'] ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: #999;">Aucune donnée de revenu disponible</p>
                <?php endif; ?>
            </div>

            <!-- Onglets de détails -->
            <div class="section">
                <div class="tabs">
                    <button class="tab active" onclick="showTab('reservations')">
                        📅 Réservations (<?= count($parkingReservations) ?>)
                    </button>
                    <button class="tab" onclick="showTab('sessions')">
                        🚗 Stationnements (<?= count($parkingSessions) ?>)
                    </button>
                    <button class="tab" onclick="showTab('subscriptions')">
                        📋 Abonnements (<?= count($parkingSubscriptions) ?>)
                    </button>
                </div>

                <!-- Onglet Réservations -->
                <div id="tab-reservations" class="tab-content active">
                    <?php if (empty($parkingReservations)): ?>
                        <div class="empty-state">
                            <p>Aucune réservation pour ce parking</p>
                        </div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Place</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Statut</th>
                                    <th>Prix</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parkingReservations as $reservation): ?>
                                    <tr>
                                        <td>User #<?= $reservation->getUserId() ?></td>
                                        <td>Place #<?= $reservation->getSlotId() ?></td>
                                        <td><?= $reservation->getStartTime()->format('d/m/Y H:i') ?></td>
                                        <td><?= $reservation->getEndTime()->format('d/m/Y H:i') ?></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($reservation->getStatus()) ?>">
                                                <?= $reservation->getStatus() ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($reservation->getTotalPrice() + $reservation->getPenalty(), 2) ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Onglet Stationnements -->
                <div id="tab-sessions" class="tab-content">
                    <?php if (empty($parkingSessions)): ?>
                        <div class="empty-state">
                            <p>Aucun stationnement pour ce parking</p>
                        </div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Entrée</th>
                                    <th>Sortie</th>
                                    <th>Statut</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parkingSessions as $session): ?>
                                    <tr>
                                        <td>User #<?= htmlspecialchars($session->getUserId()) ?></td>
                                        <td><?= $session->getEntryTime()->format('d/m/Y H:i') ?></td>
                                        <td>
                                            <?= $session->getExitTime() ?
                                                $session->getExitTime()->format('d/m/Y H:i') :
                                                '<span class="badge badge-active">En cours</span>' ?>
                                        </td>
                                        <td>
                                            <?= $session->getExitTime() ?
                                                '<span class="badge badge-completed">Terminé</span>' :
                                                '<span class="badge badge-active">Actif</span>' ?>
                                        </td>
                                        <td>
                                            <?php if ($session->getReservationId()): ?>
                                                Réservation
                                            <?php elseif ($session->getSubscriptionId()): ?>
                                                Abonnement
                                            <?php else: ?>
                                                Hors créneau
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Onglet Abonnements -->
                <div id="tab-subscriptions" class="tab-content">
                    <?php if (empty($parkingSubscriptions)): ?>
                        <div class="empty-state">
                            <p>Aucun abonnement pour ce parking</p>
                        </div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Début</th>
                                    <th>Fin</th>
                                    <th>Durée</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parkingSubscriptions as $subscription): ?>
                                    <?php $isActive = $subscription->getEndDate() >= new DateTime(); ?>
                                    <tr>
                                        <td>User #<?= htmlspecialchars($subscription->getUserId()) ?></td>
                                        <td><?= $subscription->getStartDate()->format('d/m/Y') ?></td>
                                        <td><?= $subscription->getEndDate()->format('d/m/Y') ?></td>
                                        <td><?= $subscription->getMonthsDuration() ?> mois</td>
                                        <td>
                                            <span class="badge <?= $isActive ? 'badge-active' : 'badge-completed' ?>">
                                                <?= $isActive ? 'Actif' : 'Expiré' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script>
        function showTab(tabName) {
            // Cacher tous les onglets
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Afficher l'onglet sélectionné
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
