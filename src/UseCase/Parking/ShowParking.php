<?php
namespace App\Application\Parking;

use App\Infrastructure\Repository\PDOParkingRepository;
use App\Domain\Entity\Parking;
use Exception;

class ShowParking
{
    private PDOParkingRepository $parkingRepository;

    public function __construct(PDOParkingRepository $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Récupère un parking par ID ou UUID.
     *
     * @param string $idOrUuid
     * @return Parking
     * @throws Exception si le parking n'existe pas
     */
    public function execute(string $idOrUuid): Parking
    {
        // Cherche par ID
        $parking = $this->parkingRepository->findById($idOrUuid);

        if (!$parking) {
            // Cherche par UUID
            $parking = $this->parkingRepository->findByUuid($idOrUuid);
        }

        if (!$parking) {
            throw new Exception("Parking introuvable : $idOrUuid");
        }

        return $parking;
    }
}
