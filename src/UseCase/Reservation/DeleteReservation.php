<?php
namespace App\UseCase\Reservation;

use App\Infrastructure\Repository\PDOReservationRepository;
use Exception;

class DeleteReservation
{
    private PDOReservationRepository $reservationRepository;

    public function __construct(PDOReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function execute(string $idOrUuid): void
    {
        $reservation = $this->reservationRepository->findById($idOrUuid)
            ?? $this->reservationRepository->findByUser($idOrUuid);

        if (!$reservation) {
            throw new Exception("Impossible de supprimer : Réservation introuvable ($idOrUuid)");
        }

        $this->reservationRepository->delete((string)$reservation->getId());
    }
}
