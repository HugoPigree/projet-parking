<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Reservation;

/**
 * Contrat de stockage des réservations.
 */
interface ReservationRepositoryInterface {
    public function save(Reservation $reservation): void;
    public function findById(string $id): ?Reservation;

    public function findByUser(string $userId): array;

    public function delete(string $id): void;

}
