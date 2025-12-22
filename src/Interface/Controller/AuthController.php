<?php
<<<<<<< HEAD
namespace App\Interface\Controller;

use App\UseCase\User\LoginUser;

/**
 * Contrôleur Auth (login).
 * - GET: afficher le formulaire
 * - POST: tenter la connexion
 */
class AuthController {
    public function __construct(
        private LoginUser $loginUser
    ) {}

    public function loginForm(): void {
        include __DIR__ . '/../View/login.php';
    }

    public function loginSubmit(): void {
        // TODO: lire $_POST['email'], $_POST['password']
        // appeler $this->loginUser->execute(...)
        // puis rediriger ou afficher une erreur
        echo "TODO loginSubmit()";
=======

namespace App\Interface\Controller;

use App\UseCase\User\LoginUser;
use App\UseCase\User\RegisterUser;

class AuthController
{
    public function __construct(
        private LoginUser $loginUser,
        private RegisterUser $registerUser
    ) {
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm(): void
    {
        require __DIR__ . '/../View/login.php';
    }

    /**
     * Traite la connexion
     */
    public function login(): void
    {
        $response = ['success' => false, 'message' => '', 'data' => null];

        try {
            // NE PAS sanitizer l'email - la validation est faite dans le Value Object
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $result = $this->loginUser->execute($email, $password);

            // Démarrer une session sécurisée
            $this->startSecureSession();
            
            // Régénérer l'ID de session pour prévenir Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['token'] = $result['token'];
            $_SESSION['user'] = $result['user']->toArray();

            $response['success'] = true;
            $response['message'] = 'Connexion réussie';
            $response['data'] = [
                'token' => $result['token'],
                'user' => $result['user']->toArray(),
            ];

        } catch (\InvalidArgumentException $e) {
            $response['message'] = $e->getMessage();
            http_response_code(400);
        } catch (\Exception $e) {
            $response['message'] = 'Erreur lors de la connexion';
            http_response_code(500);
        }

        $this->sendJsonResponse($response);
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegisterForm(): void
    {
        require __DIR__ . '/../View/register.php';
    }

    /**
     * Traite l'inscription
     */
    public function register(): void
    {
        $response = ['success' => false, 'message' => '', 'data' => null];

        try {
            // NE PAS sanitizer l'email
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            // Sanitizer uniquement les champs texte affichés
            $nom = $this->sanitizeInput($_POST['nom'] ?? '');
            $prenom = $this->sanitizeInput($_POST['prenom'] ?? '');
            $role = strtoupper(trim($_POST['role'] ?? 'USER'));

            $user = $this->registerUser->execute(
                $email,
                $password,
                $nom,
                $prenom,
                $role
            );

            $response['success'] = true;
            $response['message'] = 'Inscription réussie';
            $response['data'] = [
                'user' => $user->toArray(),
            ];

            http_response_code(201);

        } catch (\InvalidArgumentException $e) {
            $response['message'] = $e->getMessage();
            http_response_code(400);
        } catch (\RuntimeException $e) {
            $response['message'] = $e->getMessage();
            http_response_code(409);
        } catch (\Exception $e) {
            $response['message'] = 'Erreur lors de l\'inscription';
            http_response_code(500);
        }

        $this->sendJsonResponse($response);
    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        $this->startSecureSession();

        // Détruire la session proprement
        $_SESSION = [];
        
        // Supprimer le cookie de session
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();

        $response = [
            'success' => true,
            'message' => 'Déconnexion réussie',
        ];

        $this->sendJsonResponse($response);
    }

    /**
     * Récupère l'utilisateur courant
     */
    public function getCurrentUser(): void
    {
        $this->startSecureSession();

        $response = ['success' => false, 'message' => '', 'data' => null];

        if (isset($_SESSION['user'])) {
            $response['success'] = true;
            $response['data'] = ['user' => $_SESSION['user']];
        } else {
            $response['message'] = 'Non authentifié';
            http_response_code(401);
        }

        $this->sendJsonResponse($response);
    }

    /**
     * Démarre une session avec des paramètres de sécurité
     */
    private function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuration sécurisée des cookies de session
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }

    /**
     * Nettoie les entrées utilisateur pour l'affichage (protection XSS)
     * NE PAS utiliser sur les emails ou données validées autrement
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Envoie une réponse JSON
     */
    private function sendJsonResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
>>>>>>> origin/feat/subscription-session
    }
}
