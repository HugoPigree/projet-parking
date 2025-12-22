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

/**
 * Calcule la pénalité en cas de dépassement de créneau
 * Règle: +20€ fixe + temps supplémentaire facturé au tarif horaire
 *
 * @param DateTime $plannedEnd Heure de fin prévue
 * @param DateTime $actualEnd Heure de fin réelle
 * @param float $pricePerHour Tarif horaire du parking
 * @return float Montant de la pénalité
 */
public function calculatePenalty(DateTime $plannedEnd, DateTime $actualEnd, float $pricePerHour): float
{
    if ($actualEnd <= $plannedEnd) {
        return 0.0;
    }

    // Pénalité fixe de 20€
    $penaltyFixed = 20.0;

    // Temps supplémentaire en minutes
    $overtimeMinutes = ($actualEnd->getTimestamp() - $plannedEnd->getTimestamp()) / 60;

    // Calcul par tranche de 15 minutes
    $overtimeIntervals = ceil($overtimeMinutes / 15);
    $pricePerInterval = $pricePerHour / 4; // 1 heure = 4 tranches de 15 min
    $overtimeCost = $overtimeIntervals * $pricePerInterval;

    return $penaltyFixed + $overtimeCost;
}

/**
 * Calcule le nombre d'intervalles de 15 minutes pour une durée donnée
 *
 * @param int $durationMinutes Durée en minutes
 * @return int Nombre d'intervalles (arrondi au supérieur)
 */
public function calculate15MinIntervals(int $durationMinutes): int
{
    return (int)ceil($durationMinutes / 15);
}

}
