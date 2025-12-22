<?php

use PHPUnit\Framework\TestCase;
use App\UseCase\Reservation\CreateReservation;
use App\Infrastructure\InMemory\InMemoryReservationRepository;
use App\Infrastructure\InMemory\InMemoryParkingRepository;
use App\Domain\Service\AvailabilityService;
use App\Domain\Service\PricingService;
use App\Domain\Entity\Parking;
use DateTime;

class CreateReservationTest extends TestCase
{
    public function testReservationIsCreatedSuccessfully()
    {
        $reservationRepo = new InMemoryReservationRepository();
        $parkingRepo = new InMemoryParkingRepository();
        $parking = new Parking("p1", "owner1", "Test", "Address", 48.8, 2.3, 10, 2.5, "test","test");
        $parkingRepo->save($parking);

        $useCase = new CreateReservation(
            $reservationRepo,
            $parkingRepo,
            new AvailabilityService(),
            new PricingService()
        );

        $start = new DateTime("+1 hour");
        $end = new DateTime("+2 hours");

        $reservation = $useCase->execute("user1", "p1", $start, $end);

        $this->assertNotNull($reservation);
        $this->assertEquals("user1", $reservation->getUserId());
        $this->assertEquals("PENDING", $reservation->getStatus());
        $this->assertTrue($reservation->getPrice() > 0);
    }
}
