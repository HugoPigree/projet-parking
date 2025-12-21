<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\UseCase\ParkingSession\EnterParking;
use App\UseCase\ParkingSession\ExitParking;
use App\UseCase\Subscription\CreateSubscription;
use App\Infrastructure\InMemory\InMemoryParkingSessionRepository;
use App\Infrastructure\InMemory\InMemorySubscriptionRepository;
use App\Infrastructure\InMemory\InMemoryUserRepository;
use App\Infrastructure\InMemory\InMemoryParkingRepository;
use App\Infrastructure\InMemory\InMemoryReservationRepository;
use App\Domain\Service\AvailabilityService;
use InvalidArgumentException;

class ExitParkingTest extends TestCase
{
    private ExitParking $useCase;
    private EnterParking $enterParking;
    private InMemoryParkingSessionRepository $sessionRepo;
    private InMemorySubscriptionRepository $subscriptionRepo;
    private InMemoryUserRepository $userRepo;
    private InMemoryParkingRepository $parkingRepo;

    protected function setUp(): void
    {
        $this->sessionRepo = new InMemoryParkingSessionRepository();
        $this->subscriptionRepo = new InMemorySubscriptionRepository();
        $this->userRepo = new InMemoryUserRepository();
        $this->parkingRepo = new InMemoryParkingRepository();
        $reservationRepo = new InMemoryReservationRepository();
        $availabilityService = new AvailabilityService();

        $this->useCase = new ExitParking($this->sessionRepo, $this->userRepo);

        $this->enterParking = new EnterParking(
            $this->sessionRepo,
            $this->userRepo,
            $this->parkingRepo,
            $reservationRepo,
            $this->subscriptionRepo,
            $availabilityService
        );
    }

    public function testExitParkingSuccessfully(): void
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
        $session = $this->enterParking->execute('test_user_1', 'parking_1');

        $this->assertTrue($session->isActive());

        $this->useCase->execute('test_user_1');

        $exitedSession = $this->sessionRepo->findById($session->getId());
        $this->assertFalse($exitedSession->isActive());
        $this->assertNotNull($exitedSession->getExitTime());
    }

    public function testExitParkingThrowsExceptionForInvalidUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->useCase->execute('invalid_user');
    }

    public function testExitParkingThrowsExceptionWhenNoActiveSession(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No active parking session found for this user');

        $this->useCase->execute('test_user_1');
    }

    public function testExitParkingBySessionIdSuccessfully(): void
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
        $session = $this->enterParking->execute('test_user_1', 'parking_1');

        $this->useCase->executeBySessionId($session->getId(), 'test_user_1');

        $exitedSession = $this->sessionRepo->findById($session->getId());
        $this->assertFalse($exitedSession->isActive());
    }

    public function testExitParkingBySessionIdThrowsExceptionForWrongUser(): void
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
        $session = $this->enterParking->execute('test_user_1', 'parking_1');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only exit your own parking sessions');

        $this->useCase->executeBySessionId($session->getId(), 'test_user_2');
    }
}
