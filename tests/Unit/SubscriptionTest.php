<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Subscription;
use DateTime;

class SubscriptionTest extends TestCase
{
    public function testSubscriptionCreation(): void
    {
        $startDate = new DateTime('2025-01-01');
        $endDate = new DateTime('2025-04-01');
        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
            2 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $subscription = new Subscription(
            'sub_1',
            'user_1',
            'parking_1',
            $startDate,
            $endDate,
            $weeklySchedule,
            3
        );

        $this->assertEquals('sub_1', $subscription->getId());
        $this->assertEquals('user_1', $subscription->getUserId());
        $this->assertEquals('parking_1', $subscription->getParkingId());
        $this->assertEquals(3, $subscription->getMonthsDuration());
        $this->assertEquals($weeklySchedule, $subscription->getWeeklySchedule());
    }

    public function testSubscriptionCoversDateTimeWithinSchedule(): void
    {
        $startDate = new DateTime('2025-01-01');
        $endDate = new DateTime('2025-04-01');
        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $subscription = new Subscription(
            'sub_1',
            'user_1',
            'parking_1',
            $startDate,
            $endDate,
            $weeklySchedule,
            3
        );

        $mondayAt10 = new DateTime('2025-01-06 10:00:00');
        $this->assertTrue($subscription->coversDateTime($mondayAt10));
    }

    public function testSubscriptionDoesNotCoverDateTimeOutsideSchedule(): void
    {
        $startDate = new DateTime('2025-01-01');
        $endDate = new DateTime('2025-04-01');
        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $subscription = new Subscription(
            'sub_1',
            'user_1',
            'parking_1',
            $startDate,
            $endDate,
            $weeklySchedule,
            3
        );

        $mondayAt20 = new DateTime('2025-01-06 20:00:00');
        $this->assertFalse($subscription->coversDateTime($mondayAt20));

        $tuesdayAt10 = new DateTime('2025-01-07 10:00:00');
        $this->assertFalse($subscription->coversDateTime($tuesdayAt10));
    }

    public function testSubscriptionDoesNotCoverDateTimeOutsideDateRange(): void
    {
        $startDate = new DateTime('2025-01-01');
        $endDate = new DateTime('2025-04-01');
        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $subscription = new Subscription(
            'sub_1',
            'user_1',
            'parking_1',
            $startDate,
            $endDate,
            $weeklySchedule,
            3
        );

        $beforeStart = new DateTime('2024-12-30 10:00:00');
        $this->assertFalse($subscription->coversDateTime($beforeStart));

        $afterEnd = new DateTime('2025-05-01 10:00:00');
        $this->assertFalse($subscription->coversDateTime($afterEnd));
    }

    public function testIsActiveAt(): void
    {
        $startDate = new DateTime('2025-01-01');
        $endDate = new DateTime('2025-04-01');
        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '18:00']],
        ];

        $subscription = new Subscription(
            'sub_1',
            'user_1',
            'parking_1',
            $startDate,
            $endDate,
            $weeklySchedule,
            3
        );

        $mondayAt10 = new DateTime('2025-01-06 10:00:00');
        $this->assertTrue($subscription->isActiveAt($mondayAt10));
    }
}
