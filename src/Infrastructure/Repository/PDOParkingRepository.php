<?php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Entity\Parking;
use PDO;
use DateTime;

class PDOParkingRepository implements ParkingRepositoryInterface
{
private PDO $pdo;
   public function __construct(
        
    ) {
          $config = require __DIR__ . '/../../config/database.php';

        $this->pdo = new PDO(
            $config['dsn'],
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    public function save(Parking $parking): void
    {
        $existing = $parking->getId() ? $this->findById($parking->getId()) : null;

        if ($existing) {
            $sql = "
                UPDATE parkings SET
                    uuid = :uuid,
                    name = :name,
                    description = :description,
                    address = :address,
                    city = :city,
                    latitude = :latitude,
                    longitude = :longitude,
                    total_slots = :total_slots,
                    available_slots = :available_slots,
                    price_per_hour = :price_per_hour,
                    open_time = :open_time,
                    close_time = :close_time,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id
            ";
        } else {
            $sql = "
                INSERT INTO parkings (
                    uuid, name, description, address, city,
                    latitude, longitude, total_slots, available_slots,
                    price_per_hour, open_time, close_time, is_active,
                    created_at, updated_at
                ) VALUES (
                    :uuid, :name, :description, :address, :city,
                    :latitude, :longitude, :total_slots, :available_slots,
                    :price_per_hour, :open_time, :close_time, :is_active,
                    NOW(), NOW()
                )
            ";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'             => $parking->getId(),
            ':uuid'           => $parking->getUuid(),
            ':name'           => $parking->getName(),
            ':description'    => $parking->getDescription(),
            ':address'        => $parking->getAddress(),
            ':city'           => $parking->getCity(),
            ':latitude'       => $parking->getLatitude(),
            ':longitude'      => $parking->getLongitude(),
            ':total_slots'    => $parking->getTotalSlots(),
            ':available_slots'=> $parking->getAvailableSlots(),
            ':price_per_hour' => $parking->getPricePerHour(),
            ':open_time'      => $parking->getOpenTime(),
            ':close_time'     => $parking->getCloseTime(),
            ':is_active'      => $parking->isActive() ? 1 : 0,
        ]);
    }

    public function findById(string $id): ?Parking
    {
        $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToParking($row) : null;
    }

    public function findByUuid(string $uuid): ?Parking
    {
        $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE uuid = :uuid LIMIT 1");
        $stmt->execute([':uuid' => $uuid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToParking($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM parkings ORDER BY name ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapRowToParking($row), $rows);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM parkings WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function mapRowToParking(array $row): Parking
    {
        $parking = new Parking(
            $row['uuid'],
            $row['name'],
            $row['address'],
            $row['city'],
            (float)$row['latitude'],
            (float)$row['longitude'],
            (int)$row['total_slots'],
            (int)$row['available_slots'],
            (float)$row['price_per_hour'],
            $row['open_time'],
            $row['close_time'],
            $row['description'] ?? null
        );

        $parking->setId((int)$row['id']);

        if (isset($row['is_active'])) {
            if ($row['is_active']) {
                $parking->activate();
            } else {
                $parking->deactivate();
            }
        }

        return $parking;
    }
}
