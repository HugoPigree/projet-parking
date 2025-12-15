<?php

namespace App\UseCase\Subscription;

use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use App\Infrastructure\Repository\UserRepositoryInterface;

class ListUserSubscriptions
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

    public function execute(string $userId): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        return $this->subscriptionRepository->findByUserId($userId);
    }
}