<?php

namespace App\UseCase\Subscription;

use App\Infrastructure\Repository\SubscriptionRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Use Case : Lister tous les abonnements d'un parking
 *
 * Permet au propriétaire de voir tous les abonnements de son parking
 */
class ListParkingSubscriptions
{
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        ParkingRepositoryInterface $parkingRepository
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Liste tous les abonnements d'un parking
     *
     * @param string $parkingId ID du parking
     * @param int $ownerId ID du propriétaire (pour vérification)
     * @return array Liste des abonnements
     * @throws \InvalidArgumentException Si le parking n'existe pas ou n'appartient pas au propriétaire
     */
    public function execute(string $parkingId, int $ownerId): array
    {
        $parking = $this->parkingRepository->findById((int)$parkingId);

        if ($parking === null) {
            throw new \InvalidArgumentException('Parking non trouvé');
        }

        if ($parking->getOwnerId() !== $ownerId) {
            throw new \InvalidArgumentException('Vous n\'êtes pas propriétaire de ce parking');
        }

        return $this->subscriptionRepository->findByParkingId($parkingId);
    }
}
