<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\ParkingSession;
use DateTime;

class ParkingSessionTest extends TestCase
{
    public function testParkingSessionCreation(): void
    {
        $entryTime = new DateTime('2025-01-15 10:00:00');

        $session = new ParkingSession(
            'session_1',
            'user_1',
            'parking_1',
            $entryTime,
            null,
            'reservation_1',
            null
        );

        $this->assertEquals('session_1', $session->getId());
        $this->assertEquals('user_1', $session->getUserId());
        $this->assertEquals('parking_1', $session->getParkingId());
        $this->assertEquals($entryTime, $session->getEntryTime());
        $this->assertNull($session->getExitTime());
        $this->assertEquals('reservation_1', $session->getReservationId());
        $this->assertNull($session->getSubscriptionId());
    }

    public function testParkingSessionIsActiveWhenNoExitTime(): void
    {
        $entryTime = new DateTime('2025-01-15 10:00:00');

        $session = new ParkingSession(
            'session_1',
            'user_1',
            'parking_1',
            $entryTime
        );

        $this->assertTrue($session->isActive());
    }

    public function testParkingSessionIsNotActiveAfterExit(): void
    {
        $entryTime = new DateTime('2025-01-15 10:00:00');
        $exitTime = new DateTime('2025-01-15 12:00:00');

        $session = new ParkingSession(
            'session_1',
            'user_1',
            'parking_1',
            $entryTime,
            $exitTime
        );

        $this->assertFalse($session->isActive());
    }

    public function testExitMethodSetsExitTime(): void
    {
        $entryTime = new DateTime('2025-01-15 10:00:00');

        $session = new ParkingSession(
            'session_1',
            'user_1',
            'parking_1',
            $entryTime
        );

        $this->assertTrue($session->isActive());

        $session->exit();

        $this->assertFalse($session->isActive());
        $this->assertNotNull($session->getExitTime());
    }

    public function testGetDurationReturnsNullWhenActive(): void
    {
        $entryTime = new DateTime('2025-01-15 10:00:00');

        $session = new ParkingSession(
            'session_1',
            'user_1',
            'parking_1',
            $entryTime
        );

        $this->assertNull($session->getDuration());
        $this->assertNull($session->getDurationInMinutes());
    }

    public function testGetDurationCalculatesCorrectly(): void
    {
        $entryTime = new DateTime('2025-01-15 10:00:00');
        $exitTime = new DateTime('2025-01-15 12:30:00');

        $session = new ParkingSession(
            'session_1',
            'user_1',
            'parking_1',
            $entryTime,
            $exitTime
        );

        $expectedDurationSeconds = 150 * 60;
        $this->assertEquals($expectedDurationSeconds, $session->getDuration());
        $this->assertEquals(150, $session->getDurationInMinutes());
    }

    public function testGetDurationInMinutesRoundsUp(): void
    {
        $entryTime = new DateTime('2025-01-15 10:00:00');
        $exitTime = new DateTime('2025-01-15 10:01:30');

        $session = new ParkingSession(
            'session_1',
            'user_1',
            'parking_1',
            $entryTime,
            $exitTime
        );

        $this->assertEquals(2, $session->getDurationInMinutes());
    }
}
