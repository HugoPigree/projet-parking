<?php

namespace App\Interface;

use App\Infrastructure\Security\JwtService;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use App\Domain\Enum\UserRole;

class AuthMiddleware
{
    public function __construct(
        private JwtService $jwtService,
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Vérifie l'authentification et retourne l'utilisateur si authentifié
     * Retourne null si non authentifié
     */
    public function handle(): ?User
    {
        $token = $this->getToken();
        
        if ($token === null) {
            return null;
        }

        $userId = $this->jwtService->getUserIdFromToken($token);
        
        if ($userId === null) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    /**
     * Exige une authentification valide
     * Redirige ou retourne 401 si non authentifié
     */
    public function requireAuth(): User
    {
        $user = $this->handle();
        
        if ($user === null) {
            if ($this->isApiRequest()) {
                $this->sendUnauthorizedResponse();
            } else {
                $this->redirectToLogin();
            }
            exit;
        }

        return $user;
    }

    /**
     * Exige un rôle spécifique
     */
    public function requireRole(UserRole $role): User
    {
        $user = $this->requireAuth();
        
        if ($user->getRole() !== $role) {
            if ($this->isApiRequest()) {
                $this->sendForbiddenResponse();
            } else {
                $this->redirectToHome();
            }
            exit;
        }

        return $user;
    }

    /**
     * Exige que l'utilisateur soit un propriétaire
     */
    public function requireOwner(): User
    {
        return $this->requireRole(UserRole::OWNER);
    }

    /**
     * Exige que l'utilisateur soit un conducteur
     */
    public function requireUser(): User
    {
        return $this->requireRole(UserRole::USER);
    }

    /**
     * Récupère le token depuis les headers ou la session
     */
    private function getToken(): ?string
    {
        // Vérifier le header Authorization (API)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = $this->jwtService->extractTokenFromHeader($authHeader);
        
        if ($token !== null) {
            return $token;
        }

        // Vérifier la session (Web)
        $this->startSecureSession();

        return $_SESSION['token'] ?? null;
    }

    /**
     * Démarre une session sécurisée
     */
    private function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
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
     * Détermine si c'est une requête API
     */
    private function isApiRequest(): bool
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        return str_contains($acceptHeader, 'application/json') 
            || str_contains($contentType, 'application/json')
            || str_starts_with($uri, '/api/');
    }

    /**
     * Envoie une réponse 401 Unauthorized
     */
    private function sendUnauthorizedResponse(): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Authentification requise',
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Envoie une réponse 403 Forbidden
     */
    private function sendForbiddenResponse(): void
    {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Accès non autorisé',
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * Redirige vers la page de connexion
     */
    private function redirectToLogin(): void
    {
        header('Location: /login');
    }

    /**
     * Redirige vers la page d'accueil
     */
    private function redirectToHome(): void
    {
        header('Location: /');
    }
}
