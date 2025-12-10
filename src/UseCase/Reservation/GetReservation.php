<?php
namespace App\UseCase\Reservation;

use App\Infrastructure\Repository\PDOReservationRepository;
use App\Domain\Entity\Reservation;
use Exception;

class GetReservation
{
    private PDOReservationRepository $reservationRepository;

    public function __construct(PDOReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    /**
     * Retourne une réservation par ID ou UUID.
     *
     * @param string $idOrUuid
     * @return Reservation
     * @throws Exception
     */
    public function execute(string $idOrUuid): Reservation
    {
        $reservation = $this->reservationRepository->findById($idOrUuid)
            ?? $this->reservationRepository->findByUser($idOrUuid);

        if (!$reservation) {
            throw new Exception("Réservation introuvable : $idOrUuid");
        }

        return $reservation;
    }
}
