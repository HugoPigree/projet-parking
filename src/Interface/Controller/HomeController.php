<?php
namespace App\Interface\Controller;

use App\UseCase\Parking\SearchAvailableParkings;

/**
 * Contrôleur "home" (ex: page d'accueil qui liste les parkings).
 * Fait le lien entre HTTP -> UseCase -> Vue (MVC).
 */
class HomeController {
    public function __construct(
        private SearchAvailableParkings $searchAvailableParkings
    ) {}

    public function index(): void {
        // TODO: récupérer les parkings via use case
        $parkings = $this->searchAvailableParkings->execute();

        // inclure la vue correspondante
        $data = [ 'parkings' => $parkings ];
        include __DIR__ . '/../View/home.php';
    }
}
