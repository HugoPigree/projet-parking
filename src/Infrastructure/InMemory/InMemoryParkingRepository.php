<?php
namespace App\Infrastructure\InMemory;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Entity\Parking;

/**
 * Stockage en mémoire pour les parkings.
 */
class InMemoryParkingRepository implements ParkingRepositoryInterface {
    /** @var Parking[] */
    private array $parkings = [];

    public function __construct() {
        // TODO: peupler $this->parkingsavec des exemples si besoin
    }

    public function findAllAvailable(): array {
        // TODO: filtrer sur  hasFreeSpot()
        return $this->parkings;
    }

    public function findById(string $id): ?Parking {
        return $this->parkings[$id] ?? null;
        
    }

    public function save(Parking $parking): void {
        // TODO
    }
}
