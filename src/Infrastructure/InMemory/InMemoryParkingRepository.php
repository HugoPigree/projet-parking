<?php

namespace App\Infrastructure\InMemory;

use App\Domain\Entity\Parking;
use App\Infrastructure\Repository\ParkingRepositoryInterface;

/**
 * Implémentation InMemory du repository de parkings
 * 
 * Stockage en mémoire pour les tests et le développement
 */
class InMemoryParkingRepository implements ParkingRepositoryInterface
{
    private array $parkings = [];
    private int $nextId = 1;

    public function findById(int $id): ?Parking
    {
        return $this->parkings[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->parkings);
    }

    public function findByOwner(int $ownerId): array
    {
        return array_filter(
            $this->parkings,
            fn(Parking $parking) => $parking->getOwnerId() === $ownerId
        );
    }

    public function findNearby(float $latitude, float $longitude, float $radiusKm = 5.0): array
    {
        $nearbyParkings = [];

        foreach ($this->parkings as $parking) {
            $distance = $parking->calculateDistance($latitude, $longitude);
            if ($distance <= $radiusKm) {
                $nearbyParkings[] = $parking;
            }
        }

        // Trier par distance (plus proche en premier)
        usort($nearbyParkings, function(Parking $a, Parking $b) use ($latitude, $longitude) {
            $distanceA = $a->calculateDistance($latitude, $longitude);
            $distanceB = $b->calculateDistance($latitude, $longitude);
            return $distanceA <=> $distanceB;
        });

        return $nearbyParkings;
    }

    public function save(Parking $parking): Parking
    {
        if ($parking->getId() === null) {
            // Nouveau parking
            $parking->setId($this->nextId++);
        }

        $this->parkings[$parking->getId()] = $parking;
        return $parking;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->parkings[$id])) {
            return false;
        }

        unset($this->parkings[$id]);
        return true;
    }

    public function exists(int $id): bool
    {
        return isset($this->parkings[$id]);
    }

    /**
     * Vide le repository (utile pour les tests)
     */
    public function clear(): void
    {
        $this->parkings = [];
        $this->nextId = 1;
    }
}

