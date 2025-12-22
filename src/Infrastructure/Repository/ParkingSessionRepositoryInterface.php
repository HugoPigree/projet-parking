<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ParkingSession;

interface ParkingSessionRepositoryInterface
{
    public function save(ParkingSession $session): void;
    
    public function findById(string $id): ?ParkingSession;
    
    public function findByUserId(string $userId): array;
    
    public function findByParkingId(string $parkingId): array;
    
    public function findActiveByUserId(string $userId): array;
    
    public function findActiveByParkingId(string $parkingId): array;
    
    public function findByReservationId(string $reservationId): ?ParkingSession;
    
    public function findBySubscriptionId(string $subscriptionId): array;
    
    public function delete(string $id): void;
    
    public function findAll(): array;
}