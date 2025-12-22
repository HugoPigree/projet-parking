<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;
use App\UseCase\User\RegisterUser;

session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    header('Location: ' . ($role === 'OWNER' ? '/owner-dashboard.php' : '/user-dashboard.php'));
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $role = $_POST['role'] ?? 'USER';

    try {
        // Vérifier que les mots de passe correspondent
        if ($password !== $confirmPassword) {
            throw new Exception("Les mots de passe ne correspondent pas");
        }

        Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
        $registerUser = Dependencies::get(RegisterUser::class);

        $user = $registerUser->execute(
            $email,
            $password,
            $nom,
            $prenom,
            $role
        );

        $success = true;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Parking Partagé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .register-container {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: #666;
            font-size: 0.875rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
            font-size: 0.875rem;
        }

        input, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .role-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .role-option {
            position: relative;
        }

        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .role-option label {
            display: block;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .role-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .role-option label .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .role-option label .title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .role-option label .desc {
            font-size: 0.75rem;
            color: #666;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🅿️ Parking Partagé</h1>
            <p>Créez votre compte</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                ✅ Compte créé avec succès ! <a href="/login.php" style="color: #155724; text-decoration: underline;">Se connecter</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" id="role-user" name="role" value="USER" checked>
                        <label for="role-user">
                            <div class="icon">🚗</div>
                            <div class="title">Conducteur</div>
                            <div class="desc">Je cherche une place</div>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="role-owner" name="role" value="OWNER">
                        <label for="role-owner">
                            <div class="icon">🏢</div>
                            <div class="title">Propriétaire</div>
                            <div class="desc">Je loue mes places</div>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input
                            type="text"
                            id="nom"
                            name="nom"
                            required
                            placeholder="Dupont"
                            value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input
                            type="text"
                            id="prenom"
                            name="prenom"
                            required
                            placeholder="Jean"
                            value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="votre.email@exemple.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        minlength="8"
                    >
                    <small style="color: #666; font-size: 0.75rem;">Minimum 8 caractères</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        required
                        placeholder="••••••••"
                    >
                </div>

                <button type="submit" class="btn">Créer mon compte</button>
            </form>
        <?php endif; ?>

        <div class="login-link">
            Déjà un compte ? <a href="/login.php">Se connecter</a>
        </div>
    </div>
</body>
</html>
