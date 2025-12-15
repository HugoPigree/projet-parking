<?php

namespace App\Domain\Entity;

use DateTime;

class Subscription
{
    private string $id;
    private string $userId;
    private string $parkingId;
    private DateTime $startDate;
    private DateTime $endDate;
    private array $weeklySchedule;
    private int $monthsDuration;

    public function __construct(
        string $id,
        string $userId,
        string $parkingId,
        DateTime $startDate,
        DateTime $endDate,
        array $weeklySchedule,
        int $monthsDuration
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->weeklySchedule = $weeklySchedule;
        $this->monthsDuration = $monthsDuration;
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

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    public function getWeeklySchedule(): array
    {
        return $this->weeklySchedule;
    }

    public function getMonthsDuration(): int
    {
        return $this->monthsDuration;
    }

    public function coversDateTime(DateTime $dateTime): bool
    {
        if ($dateTime < $this->startDate || $dateTime > $this->endDate) {
            return false;
        }

        $dayOfWeek = $dateTime->format('w');
        $timeOfDay = $dateTime->format('H:i');

        if (!isset($this->weeklySchedule[$dayOfWeek])) {
            return false;
        }

        $daySchedule = $this->weeklySchedule[$dayOfWeek];
        
        foreach ($daySchedule as $timeSlot) {
            if ($timeOfDay >= $timeSlot['start'] && $timeOfDay <= $timeSlot['end']) {
                return true;
            }
        }

        return false;
    }

    public function isActiveAt(DateTime $dateTime): bool
    {
        return $this->coversDateTime($dateTime);
    }
}