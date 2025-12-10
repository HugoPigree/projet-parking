<?php
namespace App\UseCase\Parking;

use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Cas d'utilisation : l'utilisateur veut voir les parkings disponibles.
 */
class SearchAvailableParkings {
    public function __construct(
        private ParkingRepositoryInterface $parkingRepo
    ) {}

    public function execute(): array {
        // TODO: ajouter filtres zone/ville/etc
        return $this->parkingRepo->findAllAvailable();
    }
}
