<?php

namespace App\Infrastructure\InMemory;

use App\Domain\Entity\Subscription;
use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use DateTime;

class InMemorySubscriptionRepository implements SubscriptionRepositoryInterface
{
    private array $subscriptions = [];

    public function save(Subscription $subscription): void
    {
        $this->subscriptions[$subscription->getId()] = $subscription;
    }

    public function findById(string $id): ?Subscription
    {
        return $this->subscriptions[$id] ?? null;
    }

    public function findByUserId(string $userId): array
    {
        return array_filter(
            $this->subscriptions,
            fn(Subscription $subscription) => $subscription->getUserId() === $userId
        );
    }

    public function findByParkingId(string $parkingId): array
    {
        return array_filter(
            $this->subscriptions,
            fn(Subscription $subscription) => $subscription->getParkingId() === $parkingId
        );
    }

    public function findActiveByUserIdAndParkingId(string $userId, string $parkingId): ?Subscription
    {
        $now = new DateTime();
        
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getUserId() === $userId 
                && $subscription->getParkingId() === $parkingId
                && $subscription->isActiveAt($now)) {
                return $subscription;
            }
        }
        
        return null;
    }

    public function delete(string $id): void
    {
        unset($this->subscriptions[$id]);
    }

    public function findAll(): array
    {
        return array_values($this->subscriptions);
    }
}