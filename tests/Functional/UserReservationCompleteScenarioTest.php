<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Config\Dependencies;
use App\Domain\Entity\User;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;
use App\UseCase\User\RegisterUser;
use App\UseCase\Parking\CreateParking;
use App\UseCase\Reservation\CreateReservation;
use App\UseCase\ParkingSession\EnterParking;
use App\UseCase\ParkingSession\ExitParking;
use DateTime;

/**
 * Test fonctionnel : Scénario complet d'une réservation avec dépassement et pénalité
 *
 * Scénario:
 * 1. Un utilisateur crée un compte
 * 2. Un propriétaire crée un parking
 * 3. L'utilisateur réserve une place pour 2h
 * 4. L'utilisateur entre dans le parking
 * 5. L'utilisateur sort après 2h30 (dépassement de 30 min)
 * 6. Vérifier que la pénalité est appliquée (20€ + surcoût)
 */
class UserReservationCompleteScenarioTest extends TestCase
{
    protected function setUp(): void
    {
        // Utiliser le mode InMemory pour les tests
        Dependencies::setStorageType('memory');
        Dependencies::reset();
    }

    protected function tearDown(): void
    {
        Dependencies::reset();
    }

    public function testCompleteReservationScenarioWithPenalty(): void
    {
        // === ÉTAPE 1: Créer un utilisateur ===
        $registerUser = Dependencies::get(RegisterUser::class);

        $user = $registerUser->execute(
            'user@test.com',
            'password123',
            'Dupont',
            'Jean',
            'USER'
        );
        $this->assertNotNull($user);
        $this->assertEquals('user@test.com', $user->getEmail());

        // === ÉTAPE 2: Créer un propriétaire et un parking ===
        $owner = $registerUser->execute(
            'owner@test.com',
            'password123',
            'Martin',
            'Pierre',
            'OWNER'
        );
        $this->assertTrue($owner->isOwner());

        // Créer un parking
        $userRepo = Dependencies::get('userRepository');
        $parkingRepo = Dependencies::get('parkingRepository');

        $parking = new Parking(
            $owner->getId(),
            'Parking Centre-Ville',
            '10 rue de la République',
            48.8566,
            2.3522,
            50, // 50 places
            [], // Ouvert 24/7
            []  // Tarifs par défaut
        );
        $parkingRepo->save($parking);

        $this->assertNotNull($parking->getId());

        // === ÉTAPE 3: L'utilisateur réserve une place pour 2h ===
        $now = new DateTime('2025-01-15 10:00:00');
        $endTime = new DateTime('2025-01-15 12:00:00'); // 2h plus tard

        $reservation = new Reservation(
            uniqid('res_'),
            $user->getId(),
            $parking->getId(),
            1, // Slot 1
            $now,
            $endTime
        );

        // Définir le prix (8 intervalles de 15 min à 1.25€ = 10€)
        $reservation->setTotalPrice(10.0);

        $reservationRepo = Dependencies::get('reservationRepository');
        $reservationRepo->save($reservation);

        $this->assertEquals('PENDING', $reservation->getStatus());
        $this->assertEquals(10.0, $reservation->getTotalPrice());

        // === ÉTAPE 4: L'utilisateur entre dans le parking ===
        $enterParking = Dependencies::get(EnterParking::class);

        $session = $enterParking->execute(
            (string)$user->getId(),
            (string)$parking->getId(),
            $reservation->getUuid()  // Use UUID, not ID
        );

        $this->assertNotNull($session);
        $this->assertTrue($session->isActive());

        // === ÉTAPE 5: L'utilisateur sort après 2h30 (dépassement de 30 min) ===
        $exitTime = new DateTime('2025-01-15 12:30:00'); // 30 min de retard

        // Simuler la sortie
        $session->exit($exitTime);
        $sessionRepo = Dependencies::get('sessionRepository');
        $sessionRepo->save($session);

        // Calculer la pénalité manuellement (comme dans ExitParking)
        $overtimeMinutes = $reservation->calculateOvertime($exitTime);
        $this->assertEquals(30, $overtimeMinutes, 'Dépassement de 30 minutes');

        $pricingService = Dependencies::get('pricingService');
        $penalty = $pricingService->calculatePenalty(
            $endTime,
            $exitTime,
            5.0 // Tarif horaire de 5€
        );

        // Pénalité = 20€ fixe + 2 intervalles de 15min (30 min = 2*15min)
        // 2 intervalles à 5€/4 = 2 * 1.25€ = 2.50€
        // Total = 20€ + 2.50€ = 22.50€
        $this->assertEquals(22.50, $penalty, 'Pénalité calculée correctement');

        // Appliquer la pénalité à la réservation
        $reservation->setPenalty($penalty);
        $reservation->setActualEnd($exitTime);
        $reservation->complete();
        $reservationRepo->save($reservation);

        // === ÉTAPE 6: Vérifications finales ===
        $this->assertEquals('COMPLETED', $reservation->getStatus());
        $this->assertEquals(22.50, $reservation->getPenalty());
        $this->assertEquals(10.0, $reservation->getTotalPrice());

        // Le montant total à payer = prix + pénalité
        $totalToPay = $reservation->getTotalPrice() + $reservation->getPenalty();
        $this->assertEquals(32.50, $totalToPay, 'Montant total incluant la pénalité');

        // La session est maintenant fermée
        $this->assertFalse($session->isActive());
        $this->assertEquals($exitTime, $session->getExitTime());
    }

    public function testReservationWithoutOvertimeNoPenalty(): void
    {
        // Test qu'aucune pénalité n'est appliquée si sortie à l'heure
        Dependencies::reset();

        $registerUser = Dependencies::get(RegisterUser::class);
        $userRepo = Dependencies::get('userRepository');
        $parkingRepo = Dependencies::get('parkingRepository');

        // Créer utilisateur et parking
        $user = $registerUser->execute(
            'user2@test.com',
            'password123',
            'Test',
            'User',
            'USER'
        );

        $owner = $registerUser->execute(
            'owner2@test.com',
            'password123',
            'Owner',
            'Test',
            'OWNER'
        );

        $parking = new Parking(
            $owner->getId(),
            'Test Parking',
            'Test Address',
            48.8566,
            2.3522,
            10
        );
        $parkingRepo->save($parking);

        // Créer réservation
        $startTime = new DateTime('2025-01-15 14:00:00');
        $endTime = new DateTime('2025-01-15 15:00:00');

        $reservation = new Reservation(
            uniqid('res_'),
            $user->getId(),
            $parking->getId(),
            1,
            $startTime,
            $endTime
        );
        $reservation->setTotalPrice(5.0);

        $reservationRepo = Dependencies::get('reservationRepository');
        $reservationRepo->save($reservation);

        // Sortie exactement à l'heure (pas de dépassement)
        $exitTimeOnTime = new DateTime('2025-01-15 15:00:00');

        $overtimeMinutes = $reservation->calculateOvertime($exitTimeOnTime);
        $this->assertEquals(0, $overtimeMinutes, 'Aucun dépassement');

        $pricingService = Dependencies::get('pricingService');
        $penalty = $pricingService->calculatePenalty(
            $endTime,
            $exitTimeOnTime,
            5.0
        );

        $this->assertEquals(0.0, $penalty, 'Aucune pénalité si sortie à l\'heure');
    }
}
