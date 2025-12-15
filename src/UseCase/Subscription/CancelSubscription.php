<?php

namespace App\UseCase\Subscription;

use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use App\Infrastructure\Repository\UserRepositoryInterface;

class CancelSubscription
{
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userRepository = $userRepository;
    }

    public function execute(string $subscriptionId, string $userId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $subscription = $this->subscriptionRepository->findById($subscriptionId);
        if (!$subscription) {
            throw new \InvalidArgumentException('Subscription not found');
        }

        if ($subscription->getUserId() !== $userId) {
            throw new \InvalidArgumentException('Unauthorized');
        }

        $this->subscriptionRepository->delete($subscriptionId);
    }
}