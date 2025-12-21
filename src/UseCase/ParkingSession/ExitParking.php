<?php

namespace App\UseCase\ParkingSession;

use App\Infrastructure\Repository\ParkingSessionRepositoryInterface;
use App\Infrastructure\Repository\UserRepositoryInterface;
use DateTime;

class ExitParking
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

    public function execute(string $userId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $activeSessions = $this->sessionRepository->findActiveByUserId($userId);
        if (empty($activeSessions)) {
            throw new \InvalidArgumentException('No active session');
        }

        $session = reset($activeSessions);
        $session->exit();

        $this->sessionRepository->save($session);
    }

    public function executeBySessionId(string $sessionId, string $userId): void
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $session = $this->sessionRepository->findById($sessionId);
        if (!$session) {
            throw new \InvalidArgumentException('Parking session not found');
        }

        if ($session->getUserId() !== $userId) {
            throw new \InvalidArgumentException('Unauthorized');
        }

        if (!$session->isActive()) {
            throw new \InvalidArgumentException('Session already closed');
        }

        $session->exit();
        $this->sessionRepository->save($session);
    }
}