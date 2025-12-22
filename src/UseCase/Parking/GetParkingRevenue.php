<?php

namespace App\UseCase\Parking;

use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Infrastructure\Repository\SubscriptionRepositoryInterface;

/**
 * Use Case : Obtenir le chiffre d'affaire mensuel d'un parking
 *
 * Le chiffre d'affaire comprend :
 * - Toutes les réservations terminées du mois
 * - Tous les abonnements actifs du mois
 */
class GetParkingRevenue
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(
        ParkingRepositoryInterface $parkingRepository,
        ReservationRepositoryInterface $reservationRepository,
        SubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->parkingRepository = $parkingRepository;
        $this->reservationRepository = $reservationRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Calcule le chiffre d'affaire mensuel d'un parking
     * 
     * @param int $parkingId ID du parking
     * @param int $ownerId ID du propriétaire (pour vérification)
     * @param int $year Année
     * @param int $month Mois (1-12)
     * @return array Détails du chiffre d'affaire
     * @throws \InvalidArgumentException Si le parking n'existe pas
     */
    public function execute(int $parkingId, int $ownerId, int $year, int $month): array
    {
        $parking = $this->parkingRepository->findById($parkingId);

        if ($parking === null) {
            throw new \InvalidArgumentException('Parking non trouvé');
        }

        if ($parking->getOwnerId() !== $ownerId) {
            throw new \InvalidArgumentException('Vous n\'êtes pas propriétaire de ce parking');
        }

        // Dates du mois
        $startDate = new \DateTime("$year-$month-01 00:00:00");
        $endDate = new \DateTime($startDate->format('Y-m-t') . ' 23:59:59');

        // Récupérer toutes les réservations du parking
        $allReservations = $this->reservationRepository->findByParking($parkingId);

        // Calculer le revenu des réservations terminées dans le mois
        $reservationsRevenue = 0.0;
        $reservationCount = 0;
        foreach ($allReservations as $reservation) {
            // Compter uniquement les réservations terminées (COMPLETED) dont la fin est dans le mois
            if ($reservation->getStatus() === 'COMPLETED') {
                $endTime = $reservation->getActualEndTime() ?? $reservation->getEndTime();
                if ($endTime >= $startDate && $endTime <= $endDate) {
                    $reservationsRevenue += $reservation->getTotalPrice() + $reservation->getPenalty();
                    $reservationCount++;
                }
            }
        }

        // Récupérer tous les abonnements du parking
        $allSubscriptions = $this->subscriptionRepository->findByParkingId((string)$parkingId);

        // Calculer le revenu des abonnements actifs dans le mois
        // Note: Pour simplifier, on compte un abonnement s'il est actif durant le mois
        // Dans un vrai système, il faudrait un prix d'abonnement dans l'entité Subscription
        $subscriptionsRevenue = 0.0;
        $subscriptionCount = 0;
        foreach ($allSubscriptions as $subscription) {
            // Vérifier si l'abonnement est actif durant le mois
            if ($subscription->getStartDate() <= $endDate && $subscription->getEndDate() >= $startDate) {
                // Prix fictif d'abonnement (à remplacer par le vrai prix de l'abonnement)
                // Dans un système réel, Subscription devrait avoir un attribut price
                $subscriptionsRevenue += 50.0; // Prix fictif
                $subscriptionCount++;
            }
        }

        $totalRevenue = $reservationsRevenue + $subscriptionsRevenue;

        return [
            'parkingId' => $parkingId,
            'year' => $year,
            'month' => $month,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'reservationsRevenue' => $reservationsRevenue,
            'reservationCount' => $reservationCount,
            'subscriptionsRevenue' => $subscriptionsRevenue,
            'subscriptionCount' => $subscriptionCount,
            'totalRevenue' => $totalRevenue
        ];
    }
}

