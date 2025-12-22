<?php

namespace App\UseCase\ParkingSession;

use App\Infrastructure\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Use Case : Lister tous les stationnements d'un parking
 *
 * Permet au propriétaire de voir tous les stationnements de son parking
 */
class ListParkingSessionsByParking
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        ParkingRepositoryInterface $parkingRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Liste tous les stationnements d'un parking
     *
     * @param string $parkingId ID du parking
     * @param int $ownerId ID du propriétaire (pour vérification)
     * @param bool $activeOnly Si true, retourne uniquement les stationnements actifs
     * @return array Liste des stationnements
     * @throws \InvalidArgumentException Si le parking n'existe pas ou n'appartient pas au propriétaire
     */
    public function execute(string $parkingId, int $ownerId, bool $activeOnly = false): array
    {
        $parking = $this->parkingRepository->findById((int)$parkingId);

        if ($parking === null) {
            throw new \InvalidArgumentException('Parking non trouvé');
        }

        if ($parking->getOwnerId() !== $ownerId) {
            throw new \InvalidArgumentException('Vous n\'êtes pas propriétaire de ce parking');
        }

        if ($activeOnly) {
            return $this->sessionRepository->findActiveByParkingId($parkingId);
        }

        return $this->sessionRepository->findByParkingId($parkingId);
    }
}
