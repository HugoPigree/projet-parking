<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\UseCase\ParkingSession\EnterParking;
use App\UseCase\Subscription\CreateSubscription;
use App\Infrastructure\InMemory\InMemoryParkingSessionRepository;
use App\Infrastructure\InMemory\InMemorySubscriptionRepository;
use App\Infrastructure\InMemory\InMemoryUserRepository;
use App\Infrastructure\InMemory\InMemoryParkingRepository;
use App\Infrastructure\InMemory\InMemoryReservationRepository;
use App\Domain\Service\AvailabilityService;
use InvalidArgumentException;

class EnterParkingTest extends TestCase
{
    private EnterParking $useCase;
    private InMemoryParkingSessionRepository $sessionRepo;
    private InMemorySubscriptionRepository $subscriptionRepo;
    private InMemoryUserRepository $userRepo;
    private InMemoryParkingRepository $parkingRepo;
    private InMemoryReservationRepository $reservationRepo;

    protected function setUp(): void
    {
        $this->sessionRepo = new InMemoryParkingSessionRepository();
        $this->subscriptionRepo = new InMemorySubscriptionRepository();
        $this->userRepo = new InMemoryUserRepository();
        $this->parkingRepo = new InMemoryParkingRepository();
        $this->reservationRepo = new InMemoryReservationRepository();
        $availabilityService = new AvailabilityService();

        $this->useCase = new EnterParking(
            $this->sessionRepo,
            $this->userRepo,
            $this->parkingRepo,
            $this->reservationRepo,
            $this->subscriptionRepo,
            $availabilityService
        );
    }

    public function testEnterParkingWithValidSubscription(): void
    {
        $createSubscription = new CreateSubscription(
            $this->subscriptionRepo,
            $this->userRepo,
            $this->parkingRepo
        );

        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
            2 => [['start' => '09:00', 'end' => '18:00']],
            3 => [['start' => '09:00', 'end' => '18:00']],
            4 => [['start' => '09:00', 'end' => '18:00']],
            5 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $subscription = $createSubscription->execute('test_user_1', 'parking_1', $weeklySchedule, 3);

        $session = $this->useCase->execute('test_user_1', 'parking_1');

        $this->assertNotNull($session);
        $this->assertEquals('test_user_1', $session->getUserId());
        $this->assertEquals('parking_1', $session->getParkingId());
        $this->assertEquals($subscription->getId(), $session->getSubscriptionId());
        $this->assertTrue($session->isActive());
    }

    public function testEnterParkingThrowsExceptionForInvalidUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->useCase->execute('invalid_user', 'parking_1');
    }

    public function testEnterParkingThrowsExceptionForInvalidParking(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute('test_user_1', 'invalid_parking');
    }

    public function testEnterParkingThrowsExceptionWhenNoSubscriptionOrReservation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No valid reservation or subscription found for this parking');

        $this->useCase->execute('test_user_1', 'parking_1');
    }

    public function testEnterParkingThrowsExceptionWhenUserAlreadyHasActiveSession(): void
    {
        $createSubscription = new CreateSubscription(
            $this->subscriptionRepo,
            $this->userRepo,
            $this->parkingRepo
        );

        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
            2 => [['start' => '09:00', 'end' => '18:00']],
            3 => [['start' => '09:00', 'end' => '18:00']],
            4 => [['start' => '09:00', 'end' => '18:00']],
            5 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $createSubscription->execute('test_user_1', 'parking_1', $weeklySchedule, 3);
        $this->useCase->execute('test_user_1', 'parking_1');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User already has an active parking session');

        $this->useCase->execute('test_user_1', 'parking_1');
    }
}
