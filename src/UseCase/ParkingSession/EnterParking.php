<?php

namespace App\UseCase\ParkingSession;

use App\Domain\Entity\ParkingSession;
use App\Infrastructure\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use App\Domain\Service\AvailabilityService;
use DateTime;

class EnterParking
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private AvailabilityService $availabilityService;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        UserRepositoryInterface $userRepository,
        ParkingRepositoryInterface $parkingRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository,
        AvailabilityService $availabilityService
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
        $this->parkingRepository = $parkingRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->availabilityService = $availabilityService;
    }

    public function execute(string $userId, string $parkingId, ?string $reservationId = null): ParkingSession
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $parking = $this->parkingRepository->findById($parkingId);
        if (!$parking) {
            throw new \InvalidArgumentException('Parking not found');
        }

        $activeUserSessions = $this->sessionRepository->findActiveByUserId($userId);
        if (!empty($activeUserSessions)) {
            throw new \InvalidArgumentException('Active session exists');
        }

        $now = new DateTime();
        $subscriptionId = null;

        if ($reservationId) {
            $reservation = $this->reservationRepository->findById($reservationId);
            if (!$reservation) {
                throw new \InvalidArgumentException('Reservation not found');
            }
            // Convert string IDs to int for comparison
            $userIdInt = is_numeric($userId) ? (int)$userId : $userId;
            $parkingIdInt = is_numeric($parkingId) ? (int)$parkingId : $parkingId;
            if ($reservation->getUserId() != $userIdInt || $reservation->getParkingId() != $parkingIdInt) {
                throw new \InvalidArgumentException('Invalid reservation');
            }
        } else {
            $subscription = $this->subscriptionRepository->findActiveByUserIdAndParkingId($userId, $parkingId);
            if (!$subscription) {
                throw new \InvalidArgumentException('No valid access - No active subscription found for this parking');
            }
            if (!$subscription->isActiveAt($now)) {
                $dayOfWeek = $now->format('w');
                $timeOfDay = $now->format('H:i');
                throw new \InvalidArgumentException('No valid access - Subscription not active at this time (Day: ' . $dayOfWeek . ', Time: ' . $timeOfDay . ')');
            }
            $subscriptionId = $subscription->getId();
        }

        // For entry, we just check if parking is open now
        // We use $now for both start and end time
        if (!$this->availabilityService->isAvailable($parking, $now, $now)) {
            throw new \InvalidArgumentException('No spots available');
        }

        $session = new ParkingSession(
            uniqid('session_'),
            $userId,
            $parkingId,
            $now,
            null,
            $reservationId,
            $subscriptionId
        );

        $this->sessionRepository->save($session);

        return $session;
    }
}