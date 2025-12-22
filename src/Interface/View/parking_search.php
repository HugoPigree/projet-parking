<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Parkings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .search-form {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .parking-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .parking-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .parking-card h3 {
            margin-top: 0;
            color: #333;
        }
        .parking-info {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        .available-spots {
            color: #28a745;
            font-weight: bold;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Recherche de Parkings Disponibles</h1>

    <div class="search-form">
        <form method="GET" action="">
            <div class="form-group">
                <label for="latitude">Latitude :</label>
                <input type="number" step="any" id="latitude" name="latitude" 
                       value="<?= htmlspecialchars($latitude ?? '48.8566') ?>" required>
            </div>
            <div class="form-group">
                <label for="longitude">Longitude :</label>
                <input type="number" step="any" id="longitude" name="longitude" 
                       value="<?= htmlspecialchars($longitude ?? '2.3522') ?>" required>
            </div>
            <div class="form-group">
                <label for="radius">Rayon de recherche (km) :</label>
                <input type="number" step="0.1" id="radius" name="radius" value="5.0" min="0.1" max="50">
            </div>
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <?php if (isset($parkings) && !empty($parkings)): ?>
        <h2><?= count($parkings) ?> parking(s) trouvé(s)</h2>
        <div class="parking-list">
            <?php foreach ($parkings as $parking): ?>
                <div class="parking-card">
                    <h3><?= htmlspecialchars($parking->getName()) ?></h3>
                    <div class="parking-info">
                        <strong>Adresse :</strong> <?= htmlspecialchars($parking->getAddress()) ?>
                    </div>
                    <div class="parking-info">
                        <strong>Places totales :</strong> <?= $parking->getTotalSpots() ?>
                    </div>
                    <div class="parking-info">
                        <strong>Coordonnées :</strong> 
                        <?= number_format($parking->getLatitude(), 6) ?>, 
                        <?= number_format($parking->getLongitude(), 6) ?>
                    </div>
                    <div class="parking-info">
                        <strong>Distance :</strong> 
                        <?= number_format($parking->calculateDistance($latitude ?? 48.8566, $longitude ?? 2.3522), 2) ?> km
                    </div>
                    <a href="?action=show&id=<?= $parking->getId() ?>">
                        <button>Voir les détails</button>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (isset($parkings)): ?>
        <div class="no-results">
            <p>Aucun parking trouvé dans cette zone.</p>
        </div>
    <?php endif; ?>
</body>
</html>

