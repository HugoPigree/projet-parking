<?php

namespace App\Domain\Entity;

use DateTime;

class ParkingSession
{
    private string $id;
    private string $userId;
    private string $parkingId;
    private DateTime $entryTime;
    private ?DateTime $exitTime;
    private ?string $reservationId;
    private ?string $subscriptionId;

    public function __construct(
        string $id,
        string $userId,
        string $parkingId,
        DateTime $entryTime,
        ?DateTime $exitTime = null,
        ?string $reservationId = null,
        ?string $subscriptionId = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->entryTime = $entryTime;
        $this->exitTime = $exitTime;
        $this->reservationId = $reservationId;
        $this->subscriptionId = $subscriptionId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getParkingId(): string
    {
        return $this->parkingId;
    }

    public function getEntryTime(): DateTime
    {
        return $this->entryTime;
    }

    public function getExitTime(): ?DateTime
    {
        return $this->exitTime;
    }

    public function getReservationId(): ?string
    {
        return $this->reservationId;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function enter(): void
    {
        $this->entryTime = new DateTime();
    }

    public function exit(DateTime $exitTime = null): void
    {
        $this->exitTime = $exitTime ?? new DateTime();
    }

    public function isActive(): bool
    {
        return $this->exitTime === null;
    }

    public function getDuration(): ?int
    {
        if ($this->exitTime === null) {
            return null;
        }

        return $this->exitTime->getTimestamp() - $this->entryTime->getTimestamp();
    }

    public function getDurationInMinutes(): ?int
    {
        $duration = $this->getDuration();
        return $duration ? (int)ceil($duration / 60) : null;
    }
}