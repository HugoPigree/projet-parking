<?php
namespace App\Application\Parking;

use App\Infrastructure\Repository\PDOParkingRepository;
use App\Domain\Entity\Parking;

class ListParking
{
    private PDOParkingRepository $parkingRepository;

    public function __construct(PDOParkingRepository $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Retourne tous les parkings.
     *
     * @return Parking[]
     */
    public function execute(): array
    {
        return $this->parkingRepository->findAll();
    }
}
