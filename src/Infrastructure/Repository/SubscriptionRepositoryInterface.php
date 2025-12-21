<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Subscription;

interface SubscriptionRepositoryInterface
{
    public function save(Subscription $subscription): void;
    
    public function findById(string $id): ?Subscription;
    
    public function findByUserId(string $userId): array;
    
    public function findByParkingId(string $parkingId): array;
    
    public function findActiveByUserIdAndParkingId(string $userId, string $parkingId): ?Subscription;
    
    public function delete(string $id): void;
    
    public function findAll(): array;
}