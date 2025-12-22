<?php
namespace App\Infrastructure\InMemory;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Entity\Reservation;

/**
 * Stockage en mémoire pour les réservations.
 */
class InMemoryReservationRepository implements ReservationRepositoryInterface {
    /** @var Reservation[] */
    private array $reservations = [];

    public function save(Reservation $reservation): void
    {
        // Use UUID as key for in-memory storage
        $this->reservations[$reservation->getUuid()] = $reservation;
    }

   public function findById(string $id): ?Reservation
{
    // In-memory storage uses UUID as key
    return $this->reservations[$id] ?? null;
}


    public function findByUser(string $userId): array
    {
        return array_filter($this->reservations, fn($r) => $r->getUserId() === $userId);
    }

    public function findByParking(int $parkingId): array
    {
        return array_filter($this->reservations, fn($r) => $r->getParkingId() === $parkingId);
    }

    public function delete(string $id): void
    {
        unset($this->reservations[$id]);
    }
}
