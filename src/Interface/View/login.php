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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #0f3460;
            font-size: 28px;
            font-weight: 700;
        }

        .logo span {
            color: #e94560;
        }

        .logo p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0f3460;
            box-shadow: 0 0 0 3px rgba(15, 52, 96, 0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 52, 96, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .message.error {
            background: #ffe6e6;
            color: #d63031;
            border: 1px solid #fab1b1;
            display: block;
        }

        .message.success {
            background: #e6ffe6;
            color: #00b894;
            border: 1px solid #a3f7bf;
            display: block;
        }

        .links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .links a {
            color: #0f3460;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .links a:hover {
            text-decoration: underline;
            color: #e94560;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Parking<span>Partagé</span></h1>
            <p>Connectez-vous à votre compte</p>
        </div>

        <div id="message" class="message"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required placeholder="votre@email.com">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div class="links">
            <a href="/register">Pas encore de compte ? Inscrivez-vous</a>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const messageDiv = document.getElementById('message');
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageDiv.className = 'message success';
                    messageDiv.textContent = 'Connexion réussie ! Redirection...';
                    messageDiv.style.display = 'block';
                    
                    // Stocker le token
                    localStorage.setItem('token', data.data.token);
                    
                    // Rediriger vers l'accueil
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 1000);
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.textContent = data.message;
                    messageDiv.style.display = 'block';
                }
            } catch (error) {
                messageDiv.className = 'message error';
                messageDiv.textContent = 'Erreur de connexion au serveur';
                messageDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>

