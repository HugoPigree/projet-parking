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
    <title>S'abonner - <?= htmlspecialchars($parking->getName()) ?></title>
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
            max-width: 900px;
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
            max-width: 900px;
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
            margin-bottom: 1rem;
            color: #333;
        }

        .subscription-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            font-size: 1.125rem;
            margin-bottom: 1rem;
            color: #667eea;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
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

        input[type="number"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }

        .schedule-container {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
            background: #f9f9f9;
        }

        .day-schedule {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .day-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .day-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .day-name {
            font-weight: 600;
            font-size: 1rem;
            color: #333;
            flex: 1;
        }

        .time-slots {
            display: none;
            gap: 1rem;
            margin-left: 2rem;
        }

        .time-slots.active {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .time-slot-group {
            display: flex;
            flex-direction: column;
        }

        .time-slot-group label {
            font-size: 0.75rem;
            color: #666;
        }

        .time-slot-group input {
            padding: 0.5rem;
        }

        .preset-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .preset-btn {
            background: #f0f0f0;
            border: 1px solid #ddd;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .preset-btn:hover {
            background: #e0e0e0;
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

        .price-estimate {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .price-estimate h4 {
            color: #856404;
            margin-bottom: 0.5rem;
        }

        .price-value {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📅 S'abonner au Parking</h1>
            <a href="/search-parking.php" class="back-btn">← Retour</a>
        </div>
    </div>

    <div class="container">
        <!-- Informations du parking -->
        <div class="parking-info-card">
            <h2>🅿️ <?= htmlspecialchars($parking->getName()) ?></h2>
            <p><strong>📍 Adresse:</strong> <?= htmlspecialchars($parking->getAddress()) ?></p>
            <p><strong>🚗 Places totales:</strong> <?= $parking->getTotalSpots() ?></p>
        </div>

        <!-- Formulaire d'abonnement -->
        <div class="subscription-form">
            <div class="info-message">
                ℹ️ Créez votre abonnement personnalisé ! Choisissez la durée (1-12 mois) et les créneaux horaires qui vous conviennent.
            </div>

            <?php if (isset($_SESSION['subscription_error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_SESSION['subscription_error']) ?>
                </div>
                <?php unset($_SESSION['subscription_error']); ?>
            <?php endif; ?>

            <form method="POST" action="/process-subscription.php" id="subscriptionForm">
                <input type="hidden" name="parking_id" value="<?= $parking->getId() ?>">

                <!-- Durée de l'abonnement -->
                <div class="form-section">
                    <h3>1️⃣ Durée de l'abonnement</h3>
                    <div class="form-group">
                        <label for="months_duration">Nombre de mois (min: 1, max: 12)</label>
                        <input type="number" id="months_duration" name="months_duration"
                               min="1" max="12" value="1" required>
                        <small style="color: #666;">Prix estimé: <span id="priceEstimate">50.00</span> € / mois</small>
                    </div>
                </div>

                <!-- Créneaux horaires -->
                <div class="form-section">
                    <h3>2️⃣ Choisissez vos créneaux horaires</h3>

                    <div class="preset-buttons">
                        <button type="button" class="preset-btn" onclick="selectFullWeek()">
                            📅 Tous les jours (7j/7)
                        </button>
                        <button type="button" class="preset-btn" onclick="selectWeekdays()">
                            💼 Jours de semaine (Lun-Ven)
                        </button>
                        <button type="button" class="preset-btn" onclick="selectWeekend()">
                            🎉 Week-end (Sam-Dim)
                        </button>
                        <button type="button" class="preset-btn" onclick="clearAll()">
                            ❌ Tout effacer
                        </button>
                    </div>

                    <div class="schedule-container">
                        <?php foreach ($daysOfWeek as $dayNumber => $dayName): ?>
                            <div class="day-schedule">
                                <div class="day-header">
                                    <input type="checkbox" class="day-checkbox" id="day_<?= $dayNumber ?>"
                                           onchange="toggleDay(<?= $dayNumber ?>)">
                                    <label for="day_<?= $dayNumber ?>" class="day-name"><?= $dayName ?></label>
                                </div>
                                <div class="time-slots" id="slots_<?= $dayNumber ?>">
                                    <div class="time-slot-group">
                                        <label>Début</label>
                                        <input type="time" name="schedule[<?= $dayNumber ?>][start]" value="08:00">
                                    </div>
                                    <div class="time-slot-group">
                                        <label>Fin</label>
                                        <input type="time" name="schedule[<?= $dayNumber ?>][end]" value="18:00">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="price-estimate">
                    <h4>Prix total estimé</h4>
                    <div class="price-value" id="totalPrice">50.00 €</div>
                    <small>Pour <span id="monthsDisplay">1</span> mois</small>
                </div>

                <button type="submit" class="btn">
                    ✅ Valider l'abonnement
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleDay(dayNumber) {
            const checkbox = document.getElementById('day_' + dayNumber);
            const slots = document.getElementById('slots_' + dayNumber);

            if (checkbox.checked) {
                slots.classList.add('active');
            } else {
                slots.classList.remove('active');
            }
        }

        function selectFullWeek() {
            for (let i = 0; i <= 6; i++) {
                document.getElementById('day_' + i).checked = true;
                toggleDay(i);
            }
        }

        function selectWeekdays() {
            clearAll();
            for (let i = 1; i <= 5; i++) {
                document.getElementById('day_' + i).checked = true;
                toggleDay(i);
            }
        }

        function selectWeekend() {
            clearAll();
            document.getElementById('day_0').checked = true;
            document.getElementById('day_6').checked = true;
            toggleDay(0);
            toggleDay(6);
        }

        function clearAll() {
            for (let i = 0; i <= 6; i++) {
                document.getElementById('day_' + i).checked = false;
                toggleDay(i);
            }
        }

        // Mise à jour du prix en temps réel
        document.getElementById('months_duration').addEventListener('input', function() {
            const months = parseInt(this.value) || 1;
            const pricePerMonth = 50.00; // Prix fixe pour l'exemple
            const total = months * pricePerMonth;

            document.getElementById('priceEstimate').textContent = pricePerMonth.toFixed(2);
            document.getElementById('totalPrice').textContent = total.toFixed(2) + ' €';
            document.getElementById('monthsDisplay').textContent = months;
        });
    </script>
</body>
</html>
