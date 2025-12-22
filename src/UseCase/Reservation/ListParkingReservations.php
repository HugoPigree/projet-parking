<?php

namespace App\UseCase\Reservation;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Use Case : Lister toutes les réservations d'un parking
 *
 * Permet au propriétaire de voir toutes les réservations de son parking
 */
class ListParkingReservations
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        ParkingRepositoryInterface $parkingRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Liste toutes les réservations d'un parking
     *
     * @param int $parkingId ID du parking
     * @param int $ownerId ID du propriétaire (pour vérification)
     * @return array Liste des réservations
     * @throws \InvalidArgumentException Si le parking n'existe pas ou n'appartient pas au propriétaire
     */
    public function execute(int $parkingId, int $ownerId): array
    {
        $parking = $this->parkingRepository->findById($parkingId);

        if ($parking === null) {
            throw new \InvalidArgumentException('Parking non trouvé');
        }

        if ($parking->getOwnerId() !== $ownerId) {
            throw new \InvalidArgumentException('Vous n\'êtes pas propriétaire de ce parking');
        }

        return $this->reservationRepository->findByParking($parkingId);
    }
}
