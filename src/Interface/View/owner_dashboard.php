<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Propriétaire</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        .parking-list {
            margin-top: 30px;
        }
        .parking-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .parking-info h3 {
            margin-top: 0;
        }
        .parking-actions {
            display: flex;
            gap: 10px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        button:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-danger {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tableau de Bord Propriétaire</h1>
        <a href="?action=create"><button>+ Nouveau Parking</button></a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-value"><?= count($parkings ?? []) ?></div>
            <div class="stat-label">Parkings</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalSpots ?? 0 ?></div>
            <div class="stat-label">Places totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalRevenue ?? '0.00' ?>€</div>
            <div class="stat-label">Revenus ce mois</div>
        </div>
    </div>

    <div class="parking-list">
        <h2>Mes Parkings</h2>
        <?php if (isset($parkings) && !empty($parkings)): ?>
            <?php foreach ($parkings as $parking): ?>
                <div class="parking-item">
                    <div class="parking-info">
                        <h3><?= htmlspecialchars($parking->getName()) ?></h3>
                        <p><?= htmlspecialchars($parking->getAddress()) ?></p>
                        <p><strong>Places :</strong> <?= $parking->getTotalSpots() ?></p>
                    </div>
                    <div class="parking-actions">
                        <a href="?action=show&id=<?= $parking->getId() ?>">
                            <button>Voir</button>
                        </a>
                        <a href="?action=edit&id=<?= $parking->getId() ?>">
                            <button class="btn-success">Modifier</button>
                        </a>
                        <a href="?action=revenue&id=<?= $parking->getId() ?>">
                            <button>Revenus</button>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun parking créé. <a href="?action=create">Créer votre premier parking</a></p>
        <?php endif; ?>
    </div>
</body>
</html>

