<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;
use App\UseCase\Parking\SearchAvailableParkings;

session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$user = $_SESSION['user'];

// Récupérer les services
Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
$parkingRepo = Dependencies::get('parkingRepository');
$availabilityService = Dependencies::get('availabilityService');

$searchParking = new SearchAvailableParkings($parkingRepo, $availabilityService);

// Paramètres de recherche
$latitude = isset($_GET['latitude']) ? (float)$_GET['latitude'] : null;
$longitude = isset($_GET['longitude']) ? (float)$_GET['longitude'] : null;
$radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 5.0;

$parkings = [];
$searched = false;

// Effectuer la recherche si les paramètres sont fournis
if ($latitude !== null && $longitude !== null) {
    $searched = true;
    try {
        $parkings = $searchParking->execute($latitude, $longitude, $radius);
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Parkings - Parking Partagé</title>
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
            max-width: 1200px;
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .search-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .search-section h2 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.875rem;
        }

        input {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.875rem;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
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
        }

        .btn:hover {
            background: #5568d3;
        }

        .results-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .results-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .results-header h2 {
            font-size: 1.25rem;
            color: #333;
        }

        .results-count {
            color: #666;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .parking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .parking-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.2s;
        }

        .parking-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .parking-card h3 {
            font-size: 1.125rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .parking-info {
            display: flex;
            align-items: start;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
            color: #666;
        }

        .parking-info strong {
            min-width: 100px;
            color: #333;
        }

        .distance-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .spots-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .parking-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🔍 Recherche de Parkings</h1>
            <a href="/user-dashboard.php" class="back-btn">← Retour au dashboard</a>
        </div>
    </div>

    <div class="container">
        <!-- Formulaire de recherche -->
        <div class="search-section">
            <h2>Rechercher des parkings à proximité</h2>
            <form method="GET">
                <div class="form-row">
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="number" step="any" id="latitude" name="latitude"
                               value="<?= htmlspecialchars($latitude ?? '48.8566') ?>"
                               placeholder="Ex: 48.8566" required>
                    </div>
                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="number" step="any" id="longitude" name="longitude"
                               value="<?= htmlspecialchars($longitude ?? '2.3522') ?>"
                               placeholder="Ex: 2.3522" required>
                    </div>
                    <div class="form-group">
                        <label for="radius">Rayon (km)</label>
                        <input type="number" step="0.1" id="radius" name="radius"
                               value="<?= htmlspecialchars($radius ?? '5.0') ?>"
                               min="0.1" max="50" required>
                    </div>
                    <button type="submit" class="btn">🔍 Rechercher</button>
                </div>
            </form>
        </div>

        <!-- Résultats -->
        <?php if ($searched): ?>
            <div class="results-section">
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="results-header">
                    <h2>Résultats de la recherche</h2>
                    <div class="results-count">
                        <?= count($parkings) ?> parking(s) trouvé(s) dans un rayon de <?= number_format($radius, 1) ?> km
                        autour de <?= number_format($latitude, 4) ?>, <?= number_format($longitude, 4) ?>
                    </div>
                </div>

                <?php if (empty($parkings)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3>Aucun parking trouvé</h3>
                        <p>Essayez d'élargir le rayon de recherche ou de changer de position</p>
                    </div>
                <?php else: ?>
                    <div class="parking-grid">
                        <?php foreach ($parkings as $parking):
                            $distance = $parking->calculateDistance($latitude, $longitude);
                        ?>
                            <div class="parking-card">
                                <h3>🅿️ <?= htmlspecialchars($parking->getName()) ?></h3>

                                <div class="parking-info">
                                    <strong>📍 Adresse :</strong>
                                    <span><?= htmlspecialchars($parking->getAddress()) ?></span>
                                </div>

                                <div class="parking-info">
                                    <strong>🗺️ GPS :</strong>
                                    <span><?= number_format($parking->getLatitude(), 6) ?>, <?= number_format($parking->getLongitude(), 6) ?></span>
                                </div>

                                <div class="parking-info">
                                    <strong>🚗 Places :</strong>
                                    <span><?= $parking->getTotalSpots() ?> places totales</span>
                                </div>

                                <?php if ($parking->isOpenAt(new DateTime())): ?>
                                    <div class="parking-info">
                                        <strong>🕒 Statut :</strong>
                                        <span style="color: #28a745;">Ouvert maintenant</span>
                                    </div>
                                <?php else: ?>
                                    <div class="parking-info">
                                        <strong>🕒 Statut :</strong>
                                        <span style="color: #dc3545;">Fermé</span>
                                    </div>
                                <?php endif; ?>

                                <div style="margin-top: 1rem;">
                                    <span class="distance-badge">📏 <?= number_format($distance, 2) ?> km</span>
                                    <span class="spots-badge">🅿️ <?= $parking->getTotalSpots() ?> places</span>
                                </div>

                                <div style="margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                    <a href="/reserve-parking.php?parking_id=<?= $parking->getId() ?>" class="btn" style="display: block; text-align: center; text-decoration: none;">
                                        📅 Réserver
                                    </a>
                                    <a href="/subscribe-parking.php?parking_id=<?= $parking->getId() ?>" class="btn" style="display: block; text-align: center; text-decoration: none; background: #28a745;">
                                        📆 S'abonner
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
