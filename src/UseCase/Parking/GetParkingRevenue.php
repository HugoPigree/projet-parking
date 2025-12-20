<?php

namespace App\UseCase\Parking;

use App\Infrastructure\Repository\ParkingRepositoryInterface;

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
    // TODO: Ajouter ReservationRepositoryInterface et SubscriptionRepositoryInterface
    // private ReservationRepositoryInterface $reservationRepository;
    // private SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
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

        // TODO: Récupérer les réservations terminées du mois
        $reservationsRevenue = 0.0; // À implémenter avec ReservationRepository
        
        // TODO: Récupérer les abonnements actifs du mois
        $subscriptionsRevenue = 0.0; // À implémenter avec SubscriptionRepository

        $totalRevenue = $reservationsRevenue + $subscriptionsRevenue;

        return [
            'parkingId' => $parkingId,
            'year' => $year,
            'month' => $month,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'reservationsRevenue' => $reservationsRevenue,
            'subscriptionsRevenue' => $subscriptionsRevenue,
            'totalRevenue' => $totalRevenue
        ];
    }
}

