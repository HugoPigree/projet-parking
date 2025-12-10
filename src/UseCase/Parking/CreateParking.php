<?php
namespace App\Application\Parking;

use App\Domain\Entity\Parking;
use App\Infrastructure\Repository\PDOParkingRepository;
use Exception;

class CreateParking
{
    private PDOParkingRepository $parkingRepository;

    public function __construct(PDOParkingRepository $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Crée un nouveau parking.
     *
     * @param string $name
     * @param string $address
     * @param string $city
     * @param float $latitude
     * @param float $longitude
     * @param int $totalSlots
     * @param int $availableSlots
     * @param float $pricePerHour
     * @param string $openTime  Format HH:MM:SS
     * @param string $closeTime Format HH:MM:SS
     * @param string|null $description
     *
     * @return Parking
     * @throws Exception
     */
    public function execute(
        string $name,
        string $address,
        string $city,
        float $latitude,
        float $longitude,
        int $totalSlots,
        int $availableSlots,
        float $pricePerHour,
        string $openTime,
        string $closeTime,
        ?string $description = null
    ): Parking {
        // Générer un UUID simple (vous pouvez utiliser ramsey/uuid si disponible)
        $uuid = bin2hex(random_bytes(16));

        $parking = new Parking(
            $uuid,
            $name,
            $address,
            $city,
            $latitude,
            $longitude,
            $totalSlots,
            $availableSlots,
            $pricePerHour,
            $openTime,
            $closeTime,
            $description
        );

        // Enregistrer le parking dans le repository
        $this->parkingRepository->save($parking);

        return $parking;
    }
}
