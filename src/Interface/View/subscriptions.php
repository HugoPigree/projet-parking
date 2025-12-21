<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Abonnements - Parking Partagé</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .subscription-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .subscription-header { font-weight: bold; margin-bottom: 10px; }
        .subscription-details { margin: 5px 0; }
        .btn { padding: 8px 16px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .error { color: red; margin: 10px 0; }
        .success { color: green; margin: 10px 0; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #007bff; }
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

        <h1>Mes Abonnements</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div style="margin-bottom: 20px;">
            <a href="/subscriptions/create" class="btn btn-primary">Nouvel Abonnement</a>
        </div>

        <?php if (empty($subscriptions ?? [])): ?>
            <p>Aucun abonnement trouvé.</p>
        <?php else: ?>
            <?php foreach ($subscriptions as $subscription): ?>
                <div class="subscription-card">
                    <div class="subscription-header">
                        Abonnement #<?= htmlspecialchars($subscription->getId()) ?>
                    </div>
                    <div class="subscription-details">
                        <strong>Parking:</strong> <?= htmlspecialchars($subscription->getParkingId()) ?>
                    </div>
                    <div class="subscription-details">
                        <strong>Période:</strong> 
                        Du <?= $subscription->getStartDate()->format('d/m/Y') ?> 
                        au <?= $subscription->getEndDate()->format('d/m/Y') ?>
                    </div>
                    <div class="subscription-details">
                        <strong>Durée:</strong> <?= $subscription->getMonthsDuration() ?> mois
                    </div>
                    <div class="subscription-details">
                        <strong>Planning hebdomadaire:</strong>
                        <?php 
                        $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                        $schedule = $subscription->getWeeklySchedule();
                        foreach ($schedule as $day => $slots): 
                        ?>
                            <div style="margin-left: 20px;">
                                <?= $days[$day] ?>: 
                                <?php foreach ($slots as $slot): ?>
                                    <?= $slot['start'] ?> - <?= $slot['end'] ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <form method="POST" action="/subscriptions/cancel" style="display: inline;">
                            <input type="hidden" name="subscription_id" value="<?= htmlspecialchars($subscription->getId()) ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cet abonnement ?')">
                                Annuler l'abonnement
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>