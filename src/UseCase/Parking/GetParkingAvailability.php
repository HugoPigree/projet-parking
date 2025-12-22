<?php

namespace App\UseCase\Parking;

use App\Domain\Service\AvailabilityService;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Use Case : Obtenir le nombre de places disponibles dans un parking à une date précise
 */
class GetParkingAvailability
{
    private ParkingRepositoryInterface $parkingRepository;
    private AvailabilityService $availabilityService;

    public function __construct(
        ParkingRepositoryInterface $parkingRepository,
        AvailabilityService $availabilityService
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->availabilityService = $availabilityService;
    }

    /**
     * Obtient le nombre de places disponibles à un moment donné
     * 
     * @param int $parkingId ID du parking
     * @param \DateTime $timestamp Moment à vérifier
     * @param int $occupiedSpots Nombre de places occupées (sera calculé si non fourni)
     * @return array Informations sur la disponibilité
     * @throws \InvalidArgumentException Si le parking n'existe pas
     */
    public function execute(int $parkingId, \DateTime $timestamp, ?int $occupiedSpots = null): array
    {
        $parking = $this->parkingRepository->findById($parkingId);

        if ($parking === null) {
            throw new \InvalidArgumentException('Parking non trouvé');
        }

        // Si le nombre de places occupées n'est pas fourni, on le calcule
        // TODO: Calculer avec les réservations actives, stationnements et abonnements
        if ($occupiedSpots === null) {
            $occupiedSpots = 0; // À implémenter avec les repositories de réservations
        }

        $availableSpots = $this->availabilityService->getAvailableSpots($parking, $timestamp, $occupiedSpots);
        $isOpen = $parking->isOpenAt($timestamp);

        return [
            'parkingId' => $parkingId,
            'timestamp' => $timestamp->format('Y-m-d H:i:s'),
            'totalSpots' => $parking->getTotalSpots(),
            'occupiedSpots' => $occupiedSpots,
            'availableSpots' => $availableSpots,
            'isOpen' => $isOpen,
            'hasAvailableSpot' => $availableSpots > 0
        ];
    }
}

