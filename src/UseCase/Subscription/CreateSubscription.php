<?php

namespace App\UseCase\Subscription;

use App\Domain\Entity\Subscription;
use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
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
        // Validation de la durée
        if ($monthsDuration < 1 || $monthsDuration > 12) {
            throw new \InvalidArgumentException('La durée de l\'abonnement doit être entre 1 et 12 mois.');
        }

        // Validation des créneaux
        if (empty($weeklySchedule)) {
            throw new \InvalidArgumentException('Vous devez sélectionner au moins un créneau horaire.');
        }

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
            $endDate = $existingSubscription->getEndDate()->format('d/m/Y');
            throw new \InvalidArgumentException('Vous avez déjà un abonnement actif pour ce parking (expire le ' . $endDate . '). Veuillez attendre son expiration ou l\'annuler avant d\'en créer un nouveau.');
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