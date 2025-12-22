<!DOCTYPE html>
<html lang="fr">
<head>
<<<<<<< HEAD
    <meta charset="UTF-8" />
    <title>Accueil - Parkings disponibles</title>
</head>
<body>
    <h1>Parkings disponibles</h1>

    <!-- TODO: Boucler sur $data['parkings'] quand ce sera branché -->
    <p>TODO: afficher la liste des parkings (+ ville, prix/h)</p>

    <a href="?route=login">Se connecter</a> |
    <a href="?route=reservations">Mes réservations</a>
</body>
</html>
=======
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Parking Partagé</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #0f3460;
            font-size: 36px;
            margin-bottom: 10px;
        }

        .header h1 span {
            color: #e94560;
        }

        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .user-info h2 {
            color: #0f3460;
            margin-bottom: 15px;
        }

        .user-info p {
            color: #666;
            margin: 5px 0;
        }

        .user-info .role-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .user-info .role-badge.USER {
            background: #e3f2fd;
            color: #1976d2;
        }

        .user-info .role-badge.OWNER {
            background: #fff3e0;
            color: #f57c00;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0f3460 0%, #16213e 100%);
            color: white;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-danger {
            background: #e94560;
            color: white;
        }

        .not-connected {
            text-align: center;
            padding: 40px;
        }

        .not-connected h2 {
            color: #666;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Parking<span>Partagé</span></h1>
            <p>Bienvenue sur la plateforme de parking partagé</p>
        </div>

        <div id="message" class="message"></div>

        <div id="userSection" style="display: none;">
            <div class="user-info">
                <h2>Informations du compte</h2>
                <p><strong>Nom :</strong> <span id="userFullName"></span></p>
                <p><strong>Email :</strong> <span id="userEmail"></span></p>
                <p><strong>Rôle :</strong> <span id="userRole" class="role-badge"></span></p>
                <p><strong>Date d'inscription :</strong> <span id="userCreatedAt"></span></p>
            </div>

            <div class="actions">
                <button class="btn btn-danger" onclick="logout()">Se déconnecter</button>
            </div>
        </div>

        <div id="notConnectedSection" class="not-connected">
            <h2>Vous n'êtes pas connecté</h2>
            <div class="actions" style="justify-content: center;">
                <a href="/login" class="btn btn-primary">Se connecter</a>
                <a href="/register" class="btn btn-secondary">S'inscrire</a>
            </div>
        </div>
    </div>

    <script>
        // Charger les informations utilisateur au chargement de la page
        async function loadUserInfo() {
            try {
                const response = await fetch('/api/me', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.success && data.data && data.data.user) {
                    const user = data.data.user;
                    document.getElementById('userFullName').textContent = user.prenom + ' ' + user.nom;
                    document.getElementById('userEmail').textContent = user.email;
                    document.getElementById('userRole').textContent = user.role;
                    document.getElementById('userRole').className = 'role-badge ' + user.role;
                    document.getElementById('userCreatedAt').textContent = new Date(user.createdAt).toLocaleDateString('fr-FR');
                    
                    document.getElementById('userSection').style.display = 'block';
                    document.getElementById('notConnectedSection').style.display = 'none';
                } else {
                    document.getElementById('userSection').style.display = 'none';
                    document.getElementById('notConnectedSection').style.display = 'block';
                }
            } catch (error) {
                console.error('Erreur lors du chargement des infos utilisateur:', error);
                document.getElementById('userSection').style.display = 'none';
                document.getElementById('notConnectedSection').style.display = 'block';
            }
        }

        async function logout() {
            try {
                const response = await fetch('/api/logout', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Déconnexion réussie !', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } catch (error) {
                showMessage('Erreur lors de la déconnexion', 'error');
            }
        }

        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.className = 'message ' + type;
            messageDiv.textContent = text;
            messageDiv.style.display = 'block';
        }

        // Charger les infos au chargement de la page
        loadUserInfo();
    </script>
</body>
</html>


>>>>>>> origin/feat/subscription-session
