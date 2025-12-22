<?php

namespace App\UseCase\Subscription;

use App\Domain\Entity\Subscription;
use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use App\Infrastructure\Repository\UserRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;
use DateTime;

class CreateSubscription
{
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        UserRepositoryInterface $userRepository,
        ParkingRepositoryInterface $parkingRepository
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
    }

    public function execute(string $userId, string $parkingId, array $weeklySchedule, int $monthsDuration): Subscription
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $parking = $this->parkingRepository->findById($parkingId);
        if (!$parking) {
            throw new \InvalidArgumentException('Parking not found');
        }

        $existingSubscription = $this->subscriptionRepository->findActiveByUserIdAndParkingId($userId, $parkingId);
        if ($existingSubscription) {
            throw new \InvalidArgumentException('Active subscription exists');
        }

        $startDate = new DateTime();
        $endDate = clone $startDate;
        $endDate->modify("+{$monthsDuration} months");

        $subscription = new Subscription(
            uniqid('sub_'),
            $userId,
            $parkingId,
            $startDate,
            $endDate,
            $weeklySchedule,
            $monthsDuration
        );

        $this->subscriptionRepository->save($subscription);

        return $subscription;
    }
}