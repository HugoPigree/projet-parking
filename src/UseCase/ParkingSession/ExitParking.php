<?php

namespace App\UseCase\ParkingSession;

use App\Infrastructure\Repository\ParkingSessionRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Repository\ParkingRepositoryInterface;
use App\Domain\Service\PricingService;
use DateTime;

class ExitParking
{
    private ParkingSessionRepositoryInterface $sessionRepository;
    private UserRepositoryInterface $userRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private PricingService $pricingService;

    public function __construct(
        ParkingSessionRepositoryInterface $sessionRepository,
        UserRepositoryInterface $userRepository,
        ReservationRepositoryInterface $reservationRepository,
        ParkingRepositoryInterface $parkingRepository,
        PricingService $pricingService
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
        $this->reservationRepository = $reservationRepository;
        $this->parkingRepository = $parkingRepository;
        $this->pricingService = $pricingService;
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
        $exitTime = new DateTime();
        $session->exit($exitTime);

        // Calculer et appliquer la pénalité si réservation associée
        $this->applyPenaltyIfNeeded($session, $exitTime);

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

        $exitTime = new DateTime();
        $session->exit($exitTime);

        // Calculer et appliquer la pénalité si réservation associée
        $this->applyPenaltyIfNeeded($session, $exitTime);

        $this->sessionRepository->save($session);
    }

    /**
     * Applique la pénalité en cas de dépassement de créneau de réservation
     */
    private function applyPenaltyIfNeeded($session, DateTime $exitTime): void
    {
        $reservationId = $session->getReservationId();
        if (!$reservationId) {
            // Pas de réservation associée (abonnement ou accès libre)
            return;
        }

        $reservation = $this->reservationRepository->findById($reservationId);
        if (!$reservation) {
            return;
        }

        // Calculer le dépassement
        $overtimeMinutes = $reservation->calculateOvertime($exitTime);

        if ($overtimeMinutes > 0) {
            // Récupérer le parking pour obtenir le tarif
            $parking = $this->parkingRepository->findById($session->getParkingId());
            if (!$parking) {
                return;
            }

            // Tarif horaire moyen (à adapter selon la structure du parking)
            // Pour l'instant, on utilise un tarif par défaut
            // TODO: Améliorer en utilisant les PricingRules du parking
            $pricePerHour = 5.0; // Valeur par défaut

            // Calculer la pénalité: 20€ + temps supplémentaire
            $penalty = $this->pricingService->calculatePenalty(
                $reservation->getEndTime(),
                $exitTime,
                $pricePerHour
            );

            // Appliquer la pénalité à la réservation
            $reservation->setPenalty($penalty);
            $reservation->setActualEnd($exitTime);
            $reservation->complete();

            // Sauvegarder la réservation mise à jour
            $this->reservationRepository->save($reservation);
        } else {
            // Pas de dépassement, juste marquer comme terminée
            $reservation->setActualEnd($exitTime);
            $reservation->complete();
            $this->reservationRepository->save($reservation);
        }
    }
}