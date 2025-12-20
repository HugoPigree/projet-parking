<?php

namespace App\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\AvailabilityService;
use App\Domain\Entity\Parking;

class AvailabilityServiceTest extends TestCase
{
    private AvailabilityService $service;
    private Parking $parking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AvailabilityService();
        
        $this->parking = new Parking(
            ownerId: 1,
            name: 'Test Parking',
            address: 'Test Address',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 50
        );
    }

    public function testIsAvailableReturnsTrueWhenParkingIsOpenAndHasSpots(): void
    {
        $startTime = new \DateTime('2024-01-15 10:00:00');
        $endTime = new \DateTime('2024-01-15 12:00:00');

        $result = $this->service->isAvailable($this->parking, $startTime, $endTime, 25);
        
        $this->assertTrue($result);
    }

    public function testIsAvailableReturnsFalseWhenParkingIsFull(): void
    {
        $startTime = new \DateTime('2024-01-15 10:00:00');
        $endTime = new \DateTime('2024-01-15 12:00:00');

        $result = $this->service->isAvailable($this->parking, $startTime, $endTime, 50);
        
        $this->assertFalse($result);
    }

    public function testGetAvailableSpotsReturnsCorrectNumber(): void
    {
        $timestamp = new \DateTime('2024-01-15 10:00:00');
        
        $result = $this->service->getAvailableSpots($this->parking, $timestamp, 20);
        
        $this->assertEquals(30, $result);
    }

    public function testGetAvailableSpotsReturnsZeroWhenParkingIsClosed(): void
    {
        $this->parking->setOpeningHours([
            ['day' => 1, 'start' => '08:00', 'end' => '18:00']
        ]);

        $timestamp = new \DateTime('2024-01-15 20:00:00'); // Hors horaires
        
        $result = $this->service->getAvailableSpots($this->parking, $timestamp, 0);
        
        $this->assertEquals(0, $result);
    }

    public function testCanReserveReturnsTrueWhenReservationIsPossible(): void
    {
        $startTime = new \DateTime('2024-01-15 10:00:00');
        $endTime = new \DateTime('2024-01-15 12:00:00');

        $result = $this->service->canReserve($this->parking, $startTime, $endTime, 25);
        
        $this->assertTrue($result);
    }

    public function testCanReserveReturnsFalseWhenStartTimeIsAfterEndTime(): void
    {
        $startTime = new \DateTime('2024-01-15 12:00:00');
        $endTime = new \DateTime('2024-01-15 10:00:00');

        $result = $this->service->canReserve($this->parking, $startTime, $endTime, 0);
        
        $this->assertFalse($result);
    }

    public function testHasAvailableSpotReturnsTrueWhenSpotsAvailable(): void
    {
        $result = $this->service->hasAvailableSpot($this->parking, 25);
        $this->assertTrue($result);
    }

    public function testHasAvailableSpotReturnsFalseWhenFull(): void
    {
        $result = $this->service->hasAvailableSpot($this->parking, 50);
        $this->assertFalse($result);
    }
}

