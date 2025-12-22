<?php
namespace App\Application\Parking;

use App\Infrastructure\Repository\PDOParkingRepository;
use App\Domain\Entity\Parking;
use Exception;

class ModifyParking
{
    private PDOParkingRepository $parkingRepository;

    public function __construct(PDOParkingRepository $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Modifie un parking existant.
     *
     * @param string $idOrUuid ID ou UUID du parking à modifier
     * @param array $data Tableau associatif des champs à modifier :
     *                    ['name' => ..., 'address' => ..., 'city' => ..., 'latitude' => ..., 'longitude' => ..., 
     *                     'totalSlots' => ..., 'availableSlots' => ..., 'pricePerHour' => ..., 'openTime' => ..., 'closeTime' => ..., 'description' => ..., 'isActive' => ...]
     *
     * @return Parking
     * @throws Exception si le parking n'existe pas
     */
    public function execute(string $idOrUuid, array $data): Parking
    {
        // Récupération du parking existant
        $parking = $this->parkingRepository->findById($idOrUuid)
            ?? $this->parkingRepository->findByUuid($idOrUuid);

        if (!$parking) {
            throw new Exception("Parking introuvable : $idOrUuid");
        }

        
        if (isset($data['name'])) $parking->name = $data['name'];
        if (isset($data['address'])) $parking->address = $data['address'];
        if (isset($data['city'])) $parking->city = $data['city'];
        if (isset($data['latitude'])) $parking->latitude = (float)$data['latitude'];
        if (isset($data['longitude'])) $parking->longitude = (float)$data['longitude'];
        if (isset($data['totalSlots'])) $parking->totalSlots = (int)$data['totalSlots'];
        if (isset($data['availableSlots'])) $parking->availableSlots = (int)$data['availableSlots'];
        if (isset($data['pricePerHour'])) $parking->pricePerHour = (float)$data['pricePerHour'];
        if (isset($data['openTime'])) $parking->openTime = $data['openTime'];
        if (isset($data['closeTime'])) $parking->closeTime = $data['closeTime'];
        if (array_key_exists('description', $data)) $parking->description = $data['description'];
        if (isset($data['isActive'])) {
            $data['isActive'] ? $parking->activate() : $parking->deactivate();
        }

        // Met à jour updatedAt
        $parking->touch();

        
        $this->parkingRepository->save($parking);

        return $parking;
    }
}
