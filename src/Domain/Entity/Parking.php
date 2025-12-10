<?php
namespace App\Domain\Entity;

use DateTime;

class Parking
{
    public ?int $id = null;
    public string $uuid;

    public string $name;
    public ?string $description;
    public string $address;
    public string $city;

    public float $latitude;
    public float $longitude;

    public int $totalSlots;
    public int $availableSlots;

    public float $pricePerHour;

    public string $openTime;   // HH:MM:SS
    public string $closeTime;  // HH:MM:SS

    public bool $isActive = true;

    public DateTime $createdAt;
    public DateTime $updatedAt;

    public function __construct(
        string $uuid,
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
    ) {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->totalSlots = $totalSlots;
        $this->availableSlots = $availableSlots;
        $this->pricePerHour = $pricePerHour;
        $this->openTime = $openTime;
        $this->closeTime = $closeTime;
        $this->description = $description;

        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getUuid(): string { return $this->uuid; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getAddress(): string { return $this->address; }
    public function getCity(): string { return $this->city; }

    public function getLatitude(): float { return $this->latitude; }
    public function getLongitude(): float { return $this->longitude; }

    public function getTotalSlots(): int { return $this->totalSlots; }
    public function getAvailableSlots(): int { return $this->availableSlots; }

    public function getPricePerHour(): float { return $this->pricePerHour; }

    public function getOpenTime(): string { return $this->openTime; }
    public function getCloseTime(): string { return $this->closeTime; }

    public function isActive(): bool { return $this->isActive; }
    public function deactivate(): void { $this->isActive = false; }
    public function activate(): void { $this->isActive = true; }

    /**
     * Vérifie s'il reste une place disponible.
     */
    public function hasFreeSpot(): bool
    {
        return $this->availableSlots > 0;
    }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    public function touch(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'total_slots' => $this->totalSlots,
            'available_slots' => $this->availableSlots,
            'price_per_hour' => $this->pricePerHour,
            'open_time' => $this->openTime,
            'close_time' => $this->closeTime,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
