<?php
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
    }
}
