<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Dependencies;
use App\UseCase\User\LoginUser;

session_start();

// Si déjà connecté, rediriger vers le dashboard approprié
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'OWNER') {
        header('Location: /owner-dashboard.php');
    } else {
        header('Location: /user-dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        Dependencies::setStorageType($_ENV['STORAGE_MODE'] ?? 'sql');
        $loginUser = Dependencies::get(LoginUser::class);

        $result = $loginUser->execute($email, $password);

        // Stocker les informations de l'utilisateur en session
        $_SESSION['user'] = [
            'id' => $result['user']->getId(),
            'email' => $result['user']->getEmail(),
            'nom' => $result['user']->getNom(),
            'prenom' => $result['user']->getPrenom(),
            'role' => $result['user']->getRole()->value,
        ];
        $_SESSION['token'] = $result['token'];

        // Rediriger selon le rôle
        if ($result['user']->getRole()->value === 'OWNER') {
            header('Location: /owner-dashboard.php');
        } else {
            header('Location: /user-dashboard.php');
        }
        exit;

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
    <title>Connexion - Parking Partagé</title>
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

        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
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

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input:focus {
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

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            color: #999;
            font-size: 0.875rem;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-size: 0.75rem;
        }

        .demo-credentials h3 {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .demo-credentials code {
            background: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🅿️ Parking Partagé</h1>
            <p>Connectez-vous à votre compte</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
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
                >
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div class="divider">ou</div>

        <div class="register-link">
            <a href="/register.php">Créer un compte</a>
        </div>

        <div class="demo-credentials">
            <h3>🔧 Comptes de test :</h3>
            <div><strong>Propriétaire:</strong> <code>owner@test.com</code> / <code>password123</code></div>
            <div><strong>Conducteur:</strong> <code>user@test.com</code> / <code>password123</code></div>
        </div>
    </div>
</body>
</html>
