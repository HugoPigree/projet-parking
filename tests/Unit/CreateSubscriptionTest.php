<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\UseCase\Subscription\CreateSubscription;
use App\Infrastructure\InMemory\InMemorySubscriptionRepository;
use App\Infrastructure\InMemory\InMemoryUserRepository;
use App\Infrastructure\InMemory\InMemoryParkingRepository;
use InvalidArgumentException;

class CreateSubscriptionTest extends TestCase
{
    private CreateSubscription $useCase;
    private InMemorySubscriptionRepository $subscriptionRepo;
    private InMemoryUserRepository $userRepo;
    private InMemoryParkingRepository $parkingRepo;

    protected function setUp(): void
    {
        $this->subscriptionRepo = new InMemorySubscriptionRepository();
        $this->userRepo = new InMemoryUserRepository();
        $this->parkingRepo = new InMemoryParkingRepository();
        $this->useCase = new CreateSubscription(
            $this->subscriptionRepo,
            $this->userRepo,
            $this->parkingRepo
        );
    }

    public function testCreateSubscriptionSuccessfully(): void
    {
        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
            2 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $subscription = $this->useCase->execute('test_user_1', 'parking_1', $weeklySchedule, 3);

        $this->assertNotNull($subscription);
        $this->assertEquals('test_user_1', $subscription->getUserId());
        $this->assertEquals('parking_1', $subscription->getParkingId());
        $this->assertEquals(3, $subscription->getMonthsDuration());
        $this->assertEquals($weeklySchedule, $subscription->getWeeklySchedule());
    }

    public function testCreateSubscriptionThrowsExceptionForInvalidUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $this->useCase->execute('invalid_user', 'parking_1', $weeklySchedule, 3);
    }

    public function testCreateSubscriptionThrowsExceptionForInvalidParking(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parking not found');

        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $this->useCase->execute('test_user_1', 'invalid_parking', $weeklySchedule, 3);
    }

    public function testCreateSubscriptionThrowsExceptionForDuplicateActiveSubscription(): void
    {
        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $this->useCase->execute('test_user_1', 'parking_1', $weeklySchedule, 3);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User already has an active subscription for this parking');

        $this->useCase->execute('test_user_1', 'parking_1', $weeklySchedule, 3);
    }
}
