<?php

namespace App\UseCase\ParkingSession;

use App\Infrastructure\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;

/**
 * Use Case : Lister tous les stationnements d'un utilisateur
 *
 * Permet à l'utilisateur de voir son historique de stationnements
 */
class ListUserParkingSessions
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Liste tous les stationnements d'un utilisateur
     *
     * @param string $userId ID de l'utilisateur
     * @param bool $activeOnly Si true, retourne uniquement les stationnements actifs
     * @return array Liste des stationnements
     * @throws \InvalidArgumentException Si l'utilisateur n'existe pas
     */
    public function execute(string $userId, bool $activeOnly = false): array
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new \InvalidArgumentException('Utilisateur non trouvé');
        }

        if ($activeOnly) {
            return $this->sessionRepository->findActiveByUserId($userId);
        }

        return $this->sessionRepository->findByUserId($userId);
    }
}
