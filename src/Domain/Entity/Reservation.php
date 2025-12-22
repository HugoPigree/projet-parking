<?php
namespace App\Domain\Entity;

use DateTime;

class Reservation
{
    private ?int $id = null;
    private string $uuid;

    private int $userId;
    private int $parkingId;
    private int $slotId;

    private DateTime $startTime;
    private DateTime $endTime;

    private ?DateTime $actualStartTime = null;
    private ?DateTime $actualEndTime = null;

    private string $status = 'PENDING';
    private float $totalPrice = 0.0;
    private float $penalty = 0.0;

    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        string $uuid,
        int $userId,
        int $parkingId,
        int $slotId,
        DateTime $startTime,
        DateTime $endTime
    ) {
        $this->uuid = $uuid;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->slotId = $slotId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;

        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getUuid(): string { return $this->uuid; }

    public function getUserId(): int { return $this->userId; }
    public function getParkingId(): int { return $this->parkingId; }
    public function getSlotId(): int { return $this->slotId; }

    public function getStartTime(): DateTime { return $this->startTime; }
    public function getEndTime(): DateTime { return $this->endTime; }

    public function getActualStartTime(): ?DateTime { return $this->actualStartTime; }
    public function getActualEndTime(): ?DateTime { return $this->actualEndTime; }

    public function getStatus(): string { return $this->status; }

    public function getTotalPrice(): float { return $this->totalPrice; }
    public function setTotalPrice(float $price): void
    {
        $this->totalPrice = $price;
        $this->touch();
    }

    public function getPenalty(): float { return $this->penalty; }
    public function setPenalty(float $penalty): void
    {
        $this->penalty = $penalty;
        $this->touch();
    }

    public function setActualStart(DateTime $t): void
    {
        $this->actualStartTime = $t;
        $this->touch();
    }

    public function setActualEnd(DateTime $t): void
    {
        $this->actualEndTime = $t;
        $this->touch();
    }

    /** Annule la réservation */
    public function cancel(): void
    {
        $this->status = 'CANCELED';
        $this->touch();
    }

    /** Confirme la réservation */
    public function confirm(): void
    {
        $this->status = 'CONFIRMED';
        $this->touch();
    }

    /** Marque la réservation comme terminée */
    public function complete(): void
    {
        $this->status = 'COMPLETED';
        $this->actualEndTime = new DateTime();
        $this->touch();
    }

    /** Vérifie si la réservation est active (dans son créneau) */
    public function isActive(): bool
    {
        $now = new DateTime();
        return $now >= $this->startTime && $now <= $this->endTime && $this->status !== 'CANCELED';
    }

    /** Vérifie si la réservation est expirée (dépassement de fin) */
    public function isExpired(): bool
    {
        $now = new DateTime();
        return $now > $this->endTime;
    }

    /** Calcule le temps de dépassement en minutes */
    public function calculateOvertime(?DateTime $actualEnd = null): int
    {
        $actualEndTime = $actualEnd ?? $this->actualEndTime ?? new DateTime();

        if ($actualEndTime <= $this->endTime) {
            return 0;
        }

        $overtimeSeconds = $actualEndTime->getTimestamp() - $this->endTime->getTimestamp();
        return (int)ceil($overtimeSeconds / 60);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->userId,
            'parking_id' => $this->parkingId,
            'slot_id' => $this->slotId,
            'start_time' => $this->startTime->format('Y-m-d H:i:s'),
            'end_time' => $this->endTime->format('Y-m-d H:i:s'),
            'actual_start_time' => $this->actualStartTime?->format('Y-m-d H:i:s'),
            'actual_end_time' => $this->actualEndTime?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'total_price' => $this->totalPrice,
            'penalty' => $this->penalty,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
