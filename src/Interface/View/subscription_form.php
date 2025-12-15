<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Abonnement - Parking Partagé</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; }
        .btn-secondary:hover { background-color: #545b62; }
        .error { color: red; margin: 10px 0; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #007bff; }
        .schedule-day { border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 4px; }
        .schedule-day input[type="checkbox"] { margin-right: 10px; }
        .time-inputs { display: none; margin-top: 10px; }
        .time-inputs.active { display: block; }
        .time-inputs input { width: 100px; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="/">Accueil</a>
            <a href="/reservations">Réservations</a>
            <a href="/subscriptions">Abonnements</a>
            <a href="/logout">Déconnexion</a>
        </div>

        <h1>Créer un Abonnement</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/subscriptions">
            <div class="form-group">
                <label for="parking_id">Parking</label>
                <input type="text" id="parking_id" name="parking_id" required placeholder="ID du parking">
                <small>Entrez l'ID du parking pour lequel vous voulez créer un abonnement</small>
            </div>

            <div class="form-group">
                <label for="months_duration">Durée (en mois)</label>
                <select id="months_duration" name="months_duration" required>
                    <option value="1">1 mois</option>
                    <option value="3">3 mois</option>
                    <option value="6">6 mois</option>
                    <option value="12">12 mois</option>
                </select>
            </div>

            <div class="form-group">
                <label>Planning hebdomadaire</label>
                <p>Sélectionnez les jours et horaires où vous voulez utiliser le parking :</p>
                
                <?php 
                $days = [
                    0 => 'Dimanche',
                    1 => 'Lundi', 
                    2 => 'Mardi', 
                    3 => 'Mercredi', 
                    4 => 'Jeudi', 
                    5 => 'Vendredi', 
                    6 => 'Samedi'
                ];
                ?>
                
                <?php foreach ($days as $dayNum => $dayName): ?>
                    <div class="schedule-day">
                        <label>
                            <input type="checkbox" id="day_<?= $dayNum ?>_enabled" name="day_<?= $dayNum ?>_enabled" onchange="toggleTimeInputs(<?= $dayNum ?>)">
                            <?= $dayName ?>
                        </label>
                        <div id="time_inputs_<?= $dayNum ?>" class="time-inputs">
                            <label>
                                De: <input type="time" name="day_<?= $dayNum ?>_start" value="09:00">
                                À: <input type="time" name="day_<?= $dayNum ?>_end" value="18:00">
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn">Créer l'abonnement</button>
                <a href="/subscriptions" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>

    <script>
        function toggleTimeInputs(dayNum) {
            const checkbox = document.getElementById(`day_${dayNum}_enabled`);
            const timeInputs = document.getElementById(`time_inputs_${dayNum}`);
            
            if (checkbox.checked) {
                timeInputs.classList.add('active');
            } else {
                timeInputs.classList.remove('active');
            }
        }
    </script>
</body>
</html>