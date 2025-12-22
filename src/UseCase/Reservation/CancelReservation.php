<?php
namespace App\UseCase\Reservation;

use App\Infrastructure\Repository\PDOReservationRepository;
use Exception;

class CancelReservation
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
            throw new Exception("Réservation introuvable : $idOrUuid");
        }

        $reservation->cancel();
        $this->reservationRepository->save($reservation);
    }
}
