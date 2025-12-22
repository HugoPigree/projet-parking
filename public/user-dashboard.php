<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'USER') {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

// Récupérer les use cases et repositories
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$reservationRepo = Dependencies::get('reservationRepository');
$subscriptionRepo = Dependencies::get('subscriptionRepository');
$parkingRepo = Dependencies::get('parkingRepository');
$listUserParkingSessions = Dependencies::get('listUserParkingSessions');
$enterParking = Dependencies::get('enterParking');
$exitParking = Dependencies::get('exitParking');

// Récupérer les données de l'utilisateur
$reservations = $reservationRepo->findByUser((string)$user['id']);
$subscriptions = $subscriptionRepo->findByUserId((string)$user['id']);
$parkingSessions = [];
try {
    $parkingSessions = $listUserParkingSessions->execute((string)$user['id']);
} catch (\Exception $e) {
    $parkingSessions = [];
}

// Calculer les statistiques
$activeReservations = array_filter($reservations, fn($r) => $r->getStatus() === 'PENDING' || $r->getStatus() === 'CONFIRMED');
$completedReservations = array_filter($reservations, fn($r) => $r->getStatus() === 'COMPLETED');
$totalSpent = array_sum(array_map(fn($r) => $r->getTotalPrice() + $r->getPenalty(), $completedReservations));

// Séparer les sessions actives et terminées
$activeSessions = array_filter($parkingSessions, fn($s) => $s->getExitTime() === null);
$completedSessions = array_filter($parkingSessions, fn($s) => $s->getExitTime() !== null);

// Traiter les actions (Entrer/Sortir)
$message = null;
$messageType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $parkingId = $_POST['parking_id'] ?? null;
    $reservationId = $_POST['reservation_id'] ?? null;

    try {
        if ($action === 'enter' && $parkingId) {
            $result = $enterParking->execute(
                (string)$user['id'],
                (string)$parkingId,
                $reservationId ? (string)$reservationId : null
            );
            $message = "Entrée enregistrée avec succès !";
            $messageType = "success";
            header("Refresh:2");
        } elseif ($action === 'exit' && $parkingId) {
            $result = $exitParking->execute((string)$user['id'], (string)$parkingId);
            $message = "Sortie enregistrée avec succès !";
            $messageType = "success";
            header("Refresh:2");
        }
    } catch (\Exception $e) {
        $message = "Erreur : " . $e->getMessage();
        $messageType = "error";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Conducteur - Parking Partagé</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
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

        .badge-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .action-form {
            display: inline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🚗 Mon Espace Conducteur</h1>
            <div class="user-info">
                <span>👤 <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></span>
                <a href="/logout.php" class="logout-btn">Déconnexion</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message message-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Réservations Actives</h3>
                <div class="value"><?= count($activeReservations) ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Réservations</h3>
                <div class="value"><?= count($reservations) ?></div>
            </div>
            <div class="stat-card">
                <h3>Dépenses Totales</h3>
                <div class="value"><?= number_format($totalSpent, 2) ?> €</div>
            </div>
            <div class="stat-card">
                <h3>Abonnements</h3>
                <div class="value"><?= count($subscriptions) ?></div>
            </div>
            <div class="stat-card">
                <h3>Sessions Actives</h3>
                <div class="value"><?= count($activeSessions) ?></div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="section">
            <div class="section-header">
                <h2>Actions Rapides</h2>
            </div>
            <div class="quick-actions">
                <a href="/search-parking.php" class="btn">🔍 Rechercher un Parking</a>
                <!-- <a href="/subscriptions" class="btn">📋 Mes Abonnements</a> -->
            </div>
        </div>

        <!-- Sessions actives avec actions -->
        <?php if (!empty($activeSessions)): ?>
            <div class="section">
                <div class="section-header">
                    <h2>🚗 Stationnements en Cours</h2>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Parking</th>
                            <th>Entrée</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeSessions as $session):
                            $parking = $parkingRepo->findById((int)$session->getParkingId());
                        ?>
                            <tr>
                                <td><?= $parking ? htmlspecialchars($parking->getName()) : 'Parking #' . $session->getParkingId() ?></td>
                                <td><?= $session->getEntryTime()->format('d/m/Y H:i') ?></td>
                                <td>
                                    <?php if ($session->getReservationId()): ?>
                                        <span class="badge badge-confirmed">Réservation</span>
                                    <?php elseif ($session->getSubscriptionId()): ?>
                                        <span class="badge badge-active">Abonnement</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Hors créneau</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="action" value="exit">
                                        <input type="hidden" name="parking_id" value="<?= $session->getParkingId() ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Sortir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Onglets de contenu -->
        <div class="section">
            <div class="tabs">
                <button class="tab active" onclick="showTab('reservations')">
                    📅 Réservations (<?= count($reservations) ?>)
                </button>
                <button class="tab" onclick="showTab('sessions')">
                    🚗 Stationnements (<?= count($parkingSessions) ?>)
                </button>
                <button class="tab" onclick="showTab('subscriptions')">
                    📋 Abonnements (<?= count($subscriptions) ?>)
                </button>
            </div>

            <!-- Onglet Réservations -->
            <div id="tab-reservations" class="tab-content active">
                <?php if (empty($reservations)): ?>
                    <div class="empty-state">
                        <p>Aucune réservation</p>
                        <br>
                        <a href="/search-parking.php" class="btn">🔍 Rechercher un Parking</a>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Parking</th>
                                <th>Place</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Statut</th>
                                <th>Prix</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation):
                                $parking = $parkingRepo->findById($reservation->getParkingId());
                                $canEnter = in_array($reservation->getStatus(), ['PENDING', 'CONFIRMED']);
                            ?>
                                <tr>
                                    <td><?= $parking ? htmlspecialchars($parking->getName()) : 'Parking inconnu' ?></td>
                                    <td>Place #<?= $reservation->getSlotId() ?></td>
                                    <td><?= $reservation->getStartTime()->format('d/m/Y H:i') ?></td>
                                    <td><?= $reservation->getEndTime()->format('d/m/Y H:i') ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($reservation->getStatus()) ?>">
                                            <?= $reservation->getStatus() ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= number_format($reservation->getTotalPrice() + $reservation->getPenalty(), 2) ?> €
                                        <?php if ($reservation->getPenalty() > 0): ?>
                                            <br><small style="color: #dc3545;">+<?= number_format($reservation->getPenalty(), 2) ?> € pénalité</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($canEnter && $parking): ?>
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="action" value="enter">
                                                <input type="hidden" name="parking_id" value="<?= $parking->getId() ?>">
                                                <input type="hidden" name="reservation_id" value="<?= $reservation->getId() ?>">
                                                <button type="submit" class="btn btn-success btn-small">Entrer</button>
                                            </form>
                                        <?php elseif ($reservation->getStatus() === 'COMPLETED'): ?>
                                            <a href="/download-invoice.php?reservation_id=<?= $reservation->getId() ?>"
                                               class="btn btn-primary btn-small"
                                               target="_blank">
                                                📄 Facture PDF
                                            </a>
                                        <?php endif; ?>
                                    </td>
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
                        <p>Aucun stationnement</p>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Parking</th>
                                <th>Entrée</th>
                                <th>Sortie</th>
                                <th>Durée</th>
                                <th>Type</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parkingSessions as $session):
                                $parking = $parkingRepo->findById((int)$session->getParkingId());
                                $duration = null;
                                if ($session->getExitTime()) {
                                    $diff = $session->getEntryTime()->diff($session->getExitTime());
                                    $hours = $diff->h + ($diff->days * 24);
                                    $duration = sprintf("%dh %dm", $hours, $diff->i);
                                }
                            ?>
                                <tr>
                                    <td><?= $parking ? htmlspecialchars($parking->getName()) : 'Parking #' . $session->getParkingId() ?></td>
                                    <td><?= $session->getEntryTime()->format('d/m/Y H:i') ?></td>
                                    <td>
                                        <?= $session->getExitTime() ?
                                            $session->getExitTime()->format('d/m/Y H:i') :
                                            '<span class="badge badge-active">En cours</span>' ?>
                                    </td>
                                    <td><?= $duration ?? '-' ?></td>
                                    <td>
                                        <?php if ($session->getReservationId()): ?>
                                            <span class="badge badge-confirmed">Réservation</span>
                                        <?php elseif ($session->getSubscriptionId()): ?>
                                            <span class="badge badge-active">Abonnement</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Hors créneau</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $session->getExitTime() ?
                                            '<span class="badge badge-completed">Terminé</span>' :
                                            '<span class="badge badge-active">Actif</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Onglet Abonnements -->
            <div id="tab-subscriptions" class="tab-content">
                <?php if (empty($subscriptions)): ?>
                    <div class="empty-state">
                        <p>Aucun abonnement</p>
                        <br>
                        <a href="/search-parking.php" class="btn">🔍 Trouver un Parking</a>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Parking</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Durée</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $subscription):
                                $parking = $parkingRepo->findById((int)$subscription->getParkingId());
                                $isActive = $subscription->getEndDate() >= new DateTime();
                            ?>
                                <tr>
                                    <td><?= $parking ? htmlspecialchars($parking->getName()) : 'Parking #' . $subscription->getParkingId() ?></td>
                                    <td><?= $subscription->getStartDate()->format('d/m/Y') ?></td>
                                    <td><?= $subscription->getEndDate()->format('d/m/Y') ?></td>
                                    <td><?= $subscription->getMonthsDuration() ?> mois</td>
                                    <td>
                                        <span class="badge <?= $isActive ? 'badge-active' : 'badge-completed' ?>">
                                            <?= $isActive ? 'Actif' : 'Expiré' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isActive && $parking): ?>
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="action" value="enter">
                                                <input type="hidden" name="parking_id" value="<?= $parking->getId() ?>">
                                                <button type="submit" class="btn btn-success btn-small">Entrer</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
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
