<?php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Entity\Parking;
use App\Infrastructure\SQL\Database;
use PDO;
use DateTime;

class PDOParkingRepository implements ParkingRepositoryInterface
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    public function save(Parking $parking): Parking
    {
        $existing = $parking->getId() ? $this->findById($parking->getId()) : null;

        if ($existing) {
            $sql = "
                UPDATE parkings SET
                    name = :name,
                    address = :address,
                    latitude = :latitude,
                    longitude = :longitude,
                    total_spots = :total_spots,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'          => $parking->getId(),
                ':name'        => $parking->getName(),
                ':address'     => $parking->getAddress(),
                ':latitude'    => $parking->getLatitude(),
                ':longitude'   => $parking->getLongitude(),
                ':total_spots' => $parking->getTotalSpots(),
            ]);
        } else {
            $sql = "
                INSERT INTO parkings (
                    owner_id, name, address,
                    latitude, longitude, total_spots,
                    created_at, updated_at
                ) VALUES (
                    :owner_id, :name, :address,
                    :latitude, :longitude, :total_spots,
                    NOW(), NOW()
                )
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':owner_id'    => $parking->getOwnerId(),
                ':name'        => $parking->getName(),
                ':address'     => $parking->getAddress(),
                ':latitude'    => $parking->getLatitude(),
                ':longitude'   => $parking->getLongitude(),
                ':total_spots' => $parking->getTotalSpots(),
            ]);

            // Assign the newly created ID
            $newId = (int)$this->pdo->lastInsertId();
            // Use reflection to set private id property
            $reflection = new \ReflectionClass($parking);
            $idProperty = $reflection->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($parking, $newId);
        }

        return $parking;
    }

    public function findById(int $id): ?Parking
    {
        $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToParking($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM parkings ORDER BY name ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapRowToParking($row), $rows);
    }

    public function findByOwner(int $ownerId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE owner_id = :owner_id ORDER BY name ASC");
        $stmt->execute([':owner_id' => $ownerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapRowToParking($row), $rows);
    }

    public function findNearby(float $latitude, float $longitude, float $radiusKm = 5.0): array
    {
        // Haversine formula to calculate distance
        $sql = "
            SELECT *,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance
            FROM parkings
            HAVING distance < ?
            ORDER BY distance ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $latitude,
            $longitude,
            $latitude,
            $radiusKm
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapRowToParking($row), $rows);
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM parkings WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM parkings WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    private function mapRowToParking(array $row): Parking
    {
        $parking = new Parking(
            (int)$row['owner_id'],
            $row['name'],
            $row['address'],
            (float)$row['latitude'],
            (float)$row['longitude'],
            (int)$row['total_spots'],
            [], // openingHours
            [], // pricingRules
            (int)$row['id']
        );

        return $parking;
    }
}
