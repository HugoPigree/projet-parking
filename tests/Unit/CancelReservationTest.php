<?php

use PHPUnit\Framework\TestCase;
use App\UseCase\Reservation\CancelReservation;
use App\Infrastructure\InMemory\InMemoryReservationRepository;
use App\Domain\Entity\Reservation;
use DateTime;

class CancelReservationTest extends TestCase
{
    public function testCancelBeforeStart()
    {
        $repo = new InMemoryReservationRepository();

        $reservation = new Reservation(
            "res1", "user1", "p1",
            new DateTime("+1 hour"),
            new DateTime("+2 hours"),
            10,
            "PENDING",
            0
        );

        $repo->save($reservation);

        $useCase = new CancelReservation($repo);
        $useCase->execute("res1", "user1");

        $updated = $repo->findById("res1");
        $this->assertEquals("CANCELLED", $updated->getStatus());
    }
}
