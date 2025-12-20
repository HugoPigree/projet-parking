<?php

namespace App\Infrastructure\SQL;

use App\Domain\Entity\Parking;
use App\Infrastructure\Repository\ParkingRepositoryInterface;
use PDO;

/**
 * Implémentation PDO du repository de parkings
 * 
 * TODO: Compléter l'implémentation une fois la base de données configurée
 */
class PDOParkingRepository implements ParkingRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): ?Parking
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parkings WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrateParking($data);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM parkings ORDER BY id');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'hydrateParking'], $results);
    }

    public function findByOwner(int $ownerId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parkings WHERE owner_id = :ownerId ORDER BY id');
        $stmt->execute(['ownerId' => $ownerId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'hydrateParking'], $results);
    }

    public function findNearby(float $latitude, float $longitude, float $radiusKm = 5.0): array
    {
        // Utilisation de la formule de Haversine en SQL
        $stmt = $this->pdo->prepare("
            SELECT *, 
            (6371 * acos(
                cos(radians(:lat)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(:lon)) + 
                sin(radians(:lat)) * 
                sin(radians(latitude))
            )) AS distance
            FROM parkings
            HAVING distance <= :radius
            ORDER BY distance
        ");
        
        $stmt->execute([
            'lat' => $latitude,
            'lon' => $longitude,
            'radius' => $radiusKm
        ]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrateParking'], $results);
    }

    public function save(Parking $parking): Parking
    {
        if ($parking->getId() === null) {
            return $this->insert($parking);
        }

        return $this->update($parking);
    }

    private function insert(Parking $parking): Parking
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO parkings (owner_id, name, address, latitude, longitude, total_spots, opening_hours, pricing_rules, created_at, updated_at)
            VALUES (:ownerId, :name, :address, :latitude, :longitude, :totalSpots, :openingHours, :pricingRules, NOW(), NOW())
        ");

        $stmt->execute([
            'ownerId' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'totalSpots' => $parking->getTotalSpots(),
            'openingHours' => json_encode($parking->getOpeningHours()),
            'pricingRules' => json_encode($parking->getPricingRules())
        ]);

        $parking->setId((int)$this->pdo->lastInsertId());
        return $parking;
    }

    private function update(Parking $parking): Parking
    {
        $stmt = $this->pdo->prepare("
            UPDATE parkings 
            SET owner_id = :ownerId, name = :name, address = :address, 
                latitude = :latitude, longitude = :longitude, total_spots = :totalSpots,
                opening_hours = :openingHours, pricing_rules = :pricingRules, updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $parking->getId(),
            'ownerId' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'totalSpots' => $parking->getTotalSpots(),
            'openingHours' => json_encode($parking->getOpeningHours()),
            'pricingRules' => json_encode($parking->getPricingRules())
        ]);

        return $parking;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM parkings WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM parkings WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Hydrate un Parking à partir des données de la base
     */
    private function hydrateParking(array $data): Parking
    {
        $parking = new Parking(
            ownerId: (int)$data['owner_id'],
            name: $data['name'],
            address: $data['address'],
            latitude: (float)$data['latitude'],
            longitude: (float)$data['longitude'],
            totalSpots: (int)$data['total_spots'],
            openingHours: json_decode($data['opening_hours'] ?? '[]', true),
            pricingRules: json_decode($data['pricing_rules'] ?? '[]', true),
            id: (int)$data['id']
        );

        if (isset($data['created_at'])) {
            $parking->getCreatedAt()?->setTimestamp(strtotime($data['created_at']));
        }

        if (isset($data['updated_at'])) {
            $parking->getUpdatedAt()?->setTimestamp(strtotime($data['updated_at']));
        }

        return $parking;
    }
}

