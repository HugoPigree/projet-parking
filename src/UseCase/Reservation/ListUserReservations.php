<?php
namespace App\UseCase\Reservation;

use App\Infrastructure\Repository\PDOReservationRepository;
use App\Domain\Entity\Reservation;

class ListUserReservations
{
    private PDOReservationRepository $reservationRepository;

    public function __construct(PDOReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    /**
     * Retourne toutes les réservations d'un utilisateur.
     *
     * @param int $userId
     * @return Reservation[]
     */
    public function execute(int $userId): array
    {
        return $this->reservationRepository->findByUser((string)$userId);
    }
}
