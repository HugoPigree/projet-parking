<?php
namespace App\Application\Parking;

use App\Infrastructure\Repository\PDOParkingRepository;
use Exception;

class DeleteParking
{
    private PDOParkingRepository $parkingRepository;

    public function __construct(PDOParkingRepository $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Supprime un parking par ID ou UUID.
     *
     * @param string $idOrUuid
     * @throws Exception si le parking n'existe pas
     */
    public function execute(string $idOrUuid): void
    {
        // Vérifie que le parking existe
        $parking = $this->parkingRepository->findById($idOrUuid)
            ?? $this->parkingRepository->findByUuid($idOrUuid);

        if (!$parking) {
            throw new Exception("Impossible de supprimer : Parking introuvable ($idOrUuid)");
        }

        $this->parkingRepository->delete((string)$parking->getId());
    }
}
