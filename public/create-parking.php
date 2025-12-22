<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;
use App\UseCase\Parking\CreateParking;

session_start();

// Vérifier que l'utilisateur est connecté et est un propriétaire
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'OWNER') {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];
$message = null;
$messageType = null;
$success = false;

// Récupérer le use case
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$parkingRepo = Dependencies::get('parkingRepository');
$createParking = new CreateParking($parkingRepo);

// Traiter la création du parking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $latitude = (float)($_POST['latitude'] ?? 0);
        $longitude = (float)($_POST['longitude'] ?? 0);
        $totalSpots = (int)($_POST['totalSpots'] ?? 0);

        // Construire les horaires d'ouverture depuis les champs simples
        $openingHours = [];
        $openStart = $_POST['open_start'] ?? '';
        $openEnd = $_POST['open_end'] ?? '';

        if (!empty($openStart) && !empty($openEnd)) {
            $openingHours = [
                ["start" => $openStart, "end" => $openEnd]
            ];
        }

        // Construire les règles tarifaires depuis les champs simples
        $pricingRules = [];
        $pricePerHour = $_POST['price_per_hour'] ?? '';

        if (!empty($pricePerHour) && $pricePerHour > 0) {
            // Convertir en prix par intervalle de 15 minutes
            $pricePerInterval = round((float)$pricePerHour / 4, 2);
            $pricingRules = [
                ["intervalMinutes" => 15, "pricePerInterval" => $pricePerInterval]
            ];
        }

        // Créer le parking
        $parking = $createParking->execute(
            $user['id'],
            $name,
            $address,
            $latitude,
            $longitude,
            $totalSpots,
            $openingHours,
            $pricingRules
        );

        $success = true;
        $message = "Parking créé avec succès !";
        $messageType = "success";

        // Rediriger vers le dashboard après 2 secondes
        header("Refresh:2; url=/owner-dashboard.php");
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
    <title>Créer un Parking - Parking Partagé</title>
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

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
            color: #667eea;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.875rem;
            font-family: inherit;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.75rem;
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
            margin-right: 0.5rem;
        }

        .btn:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .info-box {
            background: #f8f9ff;
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }

        .info-box p {
            margin: 0.5rem 0;
            font-size: 0.875rem;
            color: #666;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🅿️ Créer un Parking</h1>
            <a href="/owner-dashboard.php" class="back-btn">← Retour au dashboard</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message message-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="section-title">📍 Informations générales</div>

                <div class="form-group">
                    <label for="name">Nom du parking *</label>
                    <input type="text" id="name" name="name" required
                           placeholder="Ex: Parking Central">
                </div>

                <div class="form-group">
                    <label for="address">Adresse *</label>
                    <textarea id="address" name="address" rows="2" required
                              placeholder="Ex: 123 Rue de la République, 75001 Paris"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="latitude">Latitude GPS *</label>
                        <input type="number" step="any" id="latitude" name="latitude" required
                               placeholder="Ex: 48.8566">
                        <small>Entre -90 et 90</small>
                    </div>

                    <div class="form-group">
                        <label for="longitude">Longitude GPS *</label>
                        <input type="number" step="any" id="longitude" name="longitude" required
                               placeholder="Ex: 2.3522">
                        <small>Entre -180 et 180</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="totalSpots">Nombre de places *</label>
                    <input type="number" id="totalSpots" name="totalSpots" min="1" required
                           placeholder="Ex: 50">
                </div>

                <div class="section-title">🕒 Horaires d'ouverture</div>

                <div class="info-box">
                    <p>💡 Laissez vide pour un parking ouvert 24h/24</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="open_start">Heure d'ouverture</label>
                        <input type="time" id="open_start" name="open_start"
                               placeholder="Ex: 08:00">
                        <small>Format : HH:MM</small>
                    </div>

                    <div class="form-group">
                        <label for="open_end">Heure de fermeture</label>
                        <input type="time" id="open_end" name="open_end"
                               placeholder="Ex: 20:00">
                        <small>Format : HH:MM</small>
                    </div>
                </div>

                <div class="section-title">💰 Tarification</div>

                <div class="info-box">
                    <p>💡 Le tarif sera appliqué par tranches de 15 minutes</p>
                    <p>Exemple : 4€/heure = 1€ par tranche de 15 min</p>
                </div>

                <div class="form-group">
                    <label for="price_per_hour">Prix par heure (€)</label>
                    <input type="number" step="0.01" id="price_per_hour" name="price_per_hour"
                           placeholder="Ex: 4.00" min="0">
                    <small>Laissez vide si gratuit</small>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn">Créer le parking</button>
                    <a href="/owner-dashboard.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
