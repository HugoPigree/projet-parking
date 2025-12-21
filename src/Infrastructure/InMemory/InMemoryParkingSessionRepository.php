<?php

namespace App\Infrastructure\InMemory;

use App\Domain\Entity\ParkingSession;
use App\Infrastructure\Repository\ParkingSessionRepositoryInterface;

class InMemoryParkingSessionRepository implements ParkingSessionRepositoryInterface
{
    private array $sessions = [];

    public function save(ParkingSession $session): void
    {
        $this->sessions[$session->getId()] = $session;
    }

    public function findById(string $id): ?ParkingSession
    {
        return $this->sessions[$id] ?? null;
    }

    public function findByUserId(string $userId): array
    {
        return array_filter(
            $this->sessions,
            fn(ParkingSession $session) => $session->getUserId() === $userId
        );
    }

    public function findByParkingId(string $parkingId): array
    {
        return array_filter(
            $this->sessions,
            fn(ParkingSession $session) => $session->getParkingId() === $parkingId
        );
    }

    public function findActiveByUserId(string $userId): array
    {
        return array_filter(
            $this->sessions,
            fn(ParkingSession $session) => $session->getUserId() === $userId && $session->isActive()
        );
    }

    public function findActiveByParkingId(string $parkingId): array
    {
        return array_filter(
            $this->sessions,
            fn(ParkingSession $session) => $session->getParkingId() === $parkingId && $session->isActive()
        );
    }

    public function findByReservationId(string $reservationId): ?ParkingSession
    {
        foreach ($this->sessions as $session) {
            if ($session->getReservationId() === $reservationId) {
                return $session;
            }
        }
        return null;
    }

    public function findBySubscriptionId(string $subscriptionId): array
    {
        return array_filter(
            $this->sessions,
            fn(ParkingSession $session) => $session->getSubscriptionId() === $subscriptionId
        );
    }

    public function delete(string $id): void
    {
        unset($this->sessions[$id]);
    }

    public function findAll(): array
    {
        return array_values($this->sessions);
    }
}