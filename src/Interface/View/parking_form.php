<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($parking) ? 'Modifier' : 'Créer' ?> un Parking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container {
            background: #f5f5f5;
            padding: 30px;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1><?= isset($parking) ? 'Modifier' : 'Créer' ?> un Parking</h1>

    <?php if (isset($success) && $success): ?>
        <div class="success">
            <?= htmlspecialchars($message ?? 'Opération réussie') ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="">
            <input type="hidden" name="action" value="<?= isset($parking) ? 'update' : 'create' ?>">
            <?php if (isset($parking)): ?>
                <input type="hidden" name="id" value="<?= $parking->getId() ?>">
            <?php endif; ?>
            <input type="hidden" name="ownerId" value="<?= $_SESSION['userId'] ?? 1 ?>">

            <div class="form-group">
                <label for="name">Nom du parking *</label>
                <input type="text" id="name" name="name" 
                       value="<?= htmlspecialchars($parking->getName() ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Adresse *</label>
                <textarea id="address" name="address" rows="2" required><?= htmlspecialchars($parking->getAddress() ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="latitude">Latitude GPS *</label>
                <input type="number" step="any" id="latitude" name="latitude" 
                       value="<?= $parking->getLatitude() ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="longitude">Longitude GPS *</label>
                <input type="number" step="any" id="longitude" name="longitude" 
                       value="<?= $parking->getLongitude() ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="totalSpots">Nombre de places *</label>
                <input type="number" id="totalSpots" name="totalSpots" 
                       value="<?= $parking->getTotalSpots() ?? '' ?>" min="1" required>
            </div>

            <div class="form-group">
                <label for="openingHours">Horaires d'ouverture (JSON)</label>
                <textarea id="openingHours" name="openingHours" rows="4"><?= htmlspecialchars(json_encode($parking->getOpeningHours() ?? [], JSON_PRETTY_PRINT)) ?></textarea>
                <small>Format: [{"day": 1, "start": "08:00", "end": "18:00"}] ou [{"start": "00:00", "end": "23:59"}]</small>
            </div>

            <div class="form-group">
                <label for="pricingRules">Règles tarifaires (JSON)</label>
                <textarea id="pricingRules" name="pricingRules" rows="4"><?= htmlspecialchars(json_encode($parking->getPricingRules() ?? [], JSON_PRETTY_PRINT)) ?></textarea>
                <small>Format: [{"intervalMinutes": 15, "pricePerInterval": 2.5}]</small>
            </div>

            <button type="submit"><?= isset($parking) ? 'Modifier' : 'Créer' ?></button>
            <a href="?action=list"><button type="button">Annuler</button></a>
        </form>
    </div>
</body>
</html>

