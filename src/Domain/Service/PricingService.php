<?php
namespace App\Domain\Service;

use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use DateInterval;
use DateTime;

/**
 * Service de calcul des prix.
 */
class PricingService {

    /**
     * Calcule un prix global (ex: pour createReservation)
     */
    public function calculatePrice(Parking $parking, DateTime $start, DateTime $end): float
    {
        // Exemple : price per hour → convert to 15 min
        $durationMinutes = ($end->getTimestamp() - $start->getTimestamp()) / 60;
        $intervalCount = ceil($durationMinutes / 15);

        $pricePer15min = $parking->getPricePerHour() / 4;

        return $intervalCount * $pricePer15min;
    }


public function breakdownPriceItems(DateTime $start, DateTime $end, Reservation $reservation)
{

    if (!$start || !$end) {
        return [];
    }

    $durationMinutes = ($end->getTimestamp() - $start->getTimestamp()) / 60;
    $intervalCount = max(1, ceil($durationMinutes / 15)); // jamais 0 pour éviter division par zéro

    $unitPrice = $reservation->getTotalPrice() / $intervalCount;

    return [
        [
            'label' => 'Intervals de 15 minutes',
            'qty'   => $intervalCount,
            'unit'  => $unitPrice
        ]
    ];
}

}
