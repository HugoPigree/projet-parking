<?php
namespace App\Domain\Service;

use App\Domain\Entity\Parking;
use DateTime;

/**
 * Service qui dit si on peut réserver ce parking à ce créneau.
 */
class AvailabilityService {
    public function isParkingBookable(Parking $parking, DateTime $start, DateTime $end): bool {
        // TODO: check horaires, places libres, cohérence start < end
        return true;
    }
}
