<?php

namespace App\UseCase\Parking;

use App\Domain\Entity\Parking;
use App\Domain\Service\AvailabilityService;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Use Case : Rechercher des parkings disponibles autour d'une coordonnée GPS
 */
class SearchAvailableParkings
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
     * Recherche des parkings disponibles autour d'une position GPS
     * 
     * @param float $latitude Latitude de la position
     * @param float $longitude Longitude de la position
     * @param float $radiusKm Rayon de recherche en kilomètres
     * @param \DateTime|null $startTime Début du créneau souhaité (optionnel)
     * @param \DateTime|null $endTime Fin du créneau souhaité (optionnel)
     * @return array Liste des parkings disponibles
     */
    public function execute(
        float $latitude,
        float $longitude,
        float $radiusKm = 5.0,
        ?\DateTime $startTime = null,
        ?\DateTime $endTime = null
    ): array {
        // Trouver les parkings proches
        $nearbyParkings = $this->parkingRepository->findNearby($latitude, $longitude, $radiusKm);

        // Si un créneau est spécifié, filtrer par disponibilité
        if ($startTime !== null && $endTime !== null) {
            return $this->filterAvailableParkings($nearbyParkings, $startTime, $endTime);
        }

        return $nearbyParkings;
    }

    /**
     * Filtre les parkings disponibles pour un créneau donné
     * 
     * @param array $parkings Liste des parkings à filtrer
     * @param \DateTime $startTime Début du créneau
     * @param \DateTime $endTime Fin du créneau
     * @return array Parkings disponibles
     */
    private function filterAvailableParkings(array $parkings, \DateTime $startTime, \DateTime $endTime): array
    {
        $availableParkings = [];

        foreach ($parkings as $parking) {
            // TODO: Calculer le nombre de places occupées en tenant compte des réservations
            // Pour l'instant, on vérifie juste si le parking est ouvert
            $occupiedSpots = 0; // À calculer avec les réservations actives
            
            if ($this->availabilityService->canReserve($parking, $startTime, $endTime, $occupiedSpots)) {
                $availableParkings[] = $parking;
            }
        }

        return $availableParkings;
    }
}

