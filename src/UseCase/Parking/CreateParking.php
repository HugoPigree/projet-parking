<?php

namespace App\UseCase\Parking;

use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Use Case : Créer un nouveau parking
 */
class CreateParking
{
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Crée un nouveau parking
     * 
     * @param int $ownerId ID du propriétaire
     * @param string $name Nom du parking
     * @param string $address Adresse du parking
     * @param float $latitude Latitude GPS
     * @param float $longitude Longitude GPS
     * @param int $totalSpots Nombre total de places
     * @param array $openingHours Horaires d'ouverture
     * @param array $pricingRules Grille tarifaire
     * @return Parking Le parking créé
     * @throws \InvalidArgumentException Si les données sont invalides
     */
    public function execute(
        int $ownerId,
        string $name,
        string $address,
        float $latitude,
        float $longitude,
        int $totalSpots,
        array $openingHours = [],
        array $pricingRules = []
    ): Parking {
        // Validation des données
        $this->validateParkingData($name, $address, $latitude, $longitude, $totalSpots);

        // Créer le parking
        $parking = new Parking(
            ownerId: $ownerId,
            name: $name,
            address: $address,
            latitude: $latitude,
            longitude: $longitude,
            totalSpots: $totalSpots,
            openingHours: $openingHours,
            pricingRules: $pricingRules
        );

        // Sauvegarder
        return $this->parkingRepository->save($parking);
    }

    /**
     * Valide les données d'un parking
     * 
     * @param string $name
     * @param string $address
     * @param float $latitude
     * @param float $longitude
     * @param int $totalSpots
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateParkingData(
        string $name,
        string $address,
        float $latitude,
        float $longitude,
        int $totalSpots
    ): void {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Le nom du parking ne peut pas être vide');
        }

        if (empty(trim($address))) {
            throw new \InvalidArgumentException('L\'adresse ne peut pas être vide');
        }

        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException('La latitude doit être entre -90 et 90');
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException('La longitude doit être entre -180 et 180');
        }

        if ($totalSpots <= 0) {
            throw new \InvalidArgumentException('Le nombre de places doit être supérieur à 0');
        }
    }
}

