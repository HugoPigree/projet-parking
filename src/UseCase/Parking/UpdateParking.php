<?php

namespace App\UseCase\Parking;

use App\Domain\Entity\Parking;
use App\Infrastructure\Repository\ParkingRepositoryInterface;

/**
 * Use Case : Mettre à jour un parking existant
 */
class UpdateParking
{
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Met à jour un parking
     * 
     * @param int $parkingId ID du parking à mettre à jour
     * @param int $ownerId ID du propriétaire (pour vérification)
     * @param array $data Données à mettre à jour
     * @return Parking Le parking mis à jour
     * @throws \InvalidArgumentException Si le parking n'existe pas ou n'appartient pas au propriétaire
     */
    public function execute(int $parkingId, int $ownerId, array $data): Parking
    {
        $parking = $this->parkingRepository->findById($parkingId);

        if ($parking === null) {
            throw new \InvalidArgumentException('Parking non trouvé');
        }

        if ($parking->getOwnerId() !== $ownerId) {
            throw new \InvalidArgumentException('Vous n\'êtes pas propriétaire de ce parking');
        }

        // Mettre à jour les propriétés fournies
        if (isset($data['name'])) {
            $parking->setName($data['name']);
        }

        if (isset($data['address'])) {
            $parking->setAddress($data['address']);
        }

        if (isset($data['latitude'])) {
            $parking->setLatitude($data['latitude']);
        }

        if (isset($data['longitude'])) {
            $parking->setLongitude($data['longitude']);
        }

        if (isset($data['totalSpots'])) {
            $parking->setTotalSpots($data['totalSpots']);
        }

        if (isset($data['openingHours'])) {
            $parking->setOpeningHours($data['openingHours']);
        }

        if (isset($data['pricingRules'])) {
            $parking->setPricingRules($data['pricingRules']);
        }

        return $this->parkingRepository->save($parking);
    }
}

