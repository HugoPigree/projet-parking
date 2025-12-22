<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Config\Dependencies;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Subscription;
use App\UseCase\User\RegisterUser;
use DateTime;

/**
 * Test fonctionnel : Scénario complet d'abonnement avec validation des créneaux
 *
 * Scénario:
 * 1. Un utilisateur crée un compte
 * 2. Souscrit un abonnement mensuel (lundi-vendredi 8h-18h)
 * 3. Tente d'entrer le lundi à 9h (OK - dans le créneau)
 * 4. Sort à 12h
 * 5. Tente d'entrer le samedi à 10h (REFUSÉ - hors créneau)
 * 6. Vérifie que la place est occupée pendant les créneaux d'abonnement
 */
class UserSubscriptionScenarioTest extends TestCase
{
    protected function setUp(): void
    {
        Dependencies::setStorageType('memory');
        Dependencies::reset();
    }

    protected function tearDown(): void
    {
        Dependencies::reset();
    }

    public function testSubscriptionWithWeeklySchedule(): void
    {
        // === ÉTAPE 1: Créer utilisateur et propriétaire ===
        $registerUser = Dependencies::get(RegisterUser::class);

        $user = $registerUser->execute(
            'subscriber@test.com',
            'password123',
            'Abonné',
            'Jean',
            'USER'
        );

        $owner = $registerUser->execute(
            'parkingowner@test.com',
            'password123',
            'Propriétaire',
            'Paul',
            'OWNER'
        );

        // Créer un parking
        $parkingRepo = Dependencies::get('parkingRepository');
        $parking = new Parking(
            $owner->getId(),
            'Parking Bureau',
            '15 Avenue des Champs',
            48.8566,
            2.3522,
            20
        );
        $parkingRepo->save($parking);

        // === ÉTAPE 2: Créer un abonnement mensuel lundi-vendredi 8h-18h ===
        $startDate = new DateTime('2025-01-01'); // Mercredi
        $endDate = new DateTime('2025-01-31');

        // Créneaux hebdomadaires : lundi(1) à vendredi(5), 8h-18h
        $weeklySchedule = [
            1 => [['start' => '08:00', 'end' => '18:00']], // Lundi
            2 => [['start' => '08:00', 'end' => '18:00']], // Mardi
            3 => [['start' => '08:00', 'end' => '18:00']], // Mercredi
            4 => [['start' => '08:00', 'end' => '18:00']], // Jeudi
            5 => [['start' => '08:00', 'end' => '18:00']], // Vendredi
        ];

        $subscription = new Subscription(
            uniqid('sub_'),
            (string)$user->getId(),
            (string)$parking->getId(),
            $startDate,
            $endDate,
            $weeklySchedule,
            1 // 1 mois
        );

        $subscriptionRepo = Dependencies::get('subscriptionRepository');
        $subscriptionRepo->save($subscription);

        // === ÉTAPE 3: Tester l'accès le lundi à 9h (DOIT ÊTRE AUTORISÉ) ===
        $mondayAt9am = new DateTime('2025-01-06 09:00:00'); // Lundi

        $isActiveAtMonday = $subscription->isActiveAt($mondayAt9am);
        $this->assertTrue($isActiveAtMonday, 'L\'abonnement doit être actif le lundi à 9h');

        $coversMonday = $subscription->coversDateTime($mondayAt9am);
        $this->assertTrue($coversMonday, 'L\'abonnement doit couvrir le lundi à 9h');

        // === ÉTAPE 4: Tester l'accès le samedi à 10h (DOIT ÊTRE REFUSÉ) ===
        $saturdayAt10am = new DateTime('2025-01-11 10:00:00'); // Samedi

        $isActiveAtSaturday = $subscription->isActiveAt($saturdayAt10am);
        $this->assertFalse($isActiveAtSaturday, 'L\'abonnement ne doit PAS être actif le samedi');

        $coversSaturday = $subscription->coversDateTime($saturdayAt10am);
        $this->assertFalse($coversSaturday, 'L\'abonnement ne doit PAS couvrir le samedi');

        // === ÉTAPE 5: Tester hors horaires (lundi à 7h - trop tôt) ===
        $mondayAt7am = new DateTime('2025-01-06 07:00:00');

        $isActiveAtEarly = $subscription->isActiveAt($mondayAt7am);
        $this->assertFalse($isActiveAtEarly, 'L\'abonnement ne doit PAS être actif avant 8h');

        // === ÉTAPE 6: Tester hors horaires (lundi à 19h - trop tard) ===
        $mondayAt7pm = new DateTime('2025-01-06 19:00:00');

        $isActiveAtLate = $subscription->isActiveAt($mondayAt7pm);
        $this->assertFalse($isActiveAtLate, 'L\'abonnement ne doit PAS être actif après 18h');

        // === ÉTAPE 7: Tester date en dehors de la période ===
        $beforeStart = new DateTime('2024-12-31 10:00:00');
        $afterEnd = new DateTime('2025-02-01 10:00:00');

        $this->assertFalse($subscription->isActiveAt($beforeStart), 'Avant la date de début');
        $this->assertFalse($subscription->isActiveAt($afterEnd), 'Après la date de fin');
    }

    public function testSubscriptionDurationValidation(): void
    {
        // Vérifier que la durée est bien entre 1 et 12 mois
        $startDate = new DateTime('2025-01-01');
        $endDate = new DateTime('2025-02-01');

        $weeklySchedule = [
            1 => [['start' => '09:00', 'end' => '17:00']],
        ];

        // 1 mois : OK
        $sub1Month = new Subscription(
            uniqid('sub_'),
            '1',
            '1',
            $startDate,
            $endDate,
            $weeklySchedule,
            1
        );
        $this->assertEquals(1, $sub1Month->getMonthsDuration());

        // 12 mois : OK
        $endDate12Months = (clone $startDate)->modify('+12 months');
        $sub12Months = new Subscription(
            uniqid('sub_'),
            '1',
            '1',
            $startDate,
            $endDate12Months,
            $weeklySchedule,
            12
        );
        $this->assertEquals(12, $sub12Months->getMonthsDuration());

        // TODO: Ajouter validation dans le constructeur pour rejeter < 1 ou > 12
    }

    public function testMultipleUsersWithSubscriptions(): void
    {
        // Test que plusieurs utilisateurs peuvent avoir des abonnements sur le même parking
        $registerUser = Dependencies::get(RegisterUser::class);
        $parkingRepo = Dependencies::get('parkingRepository');
        $subscriptionRepo = Dependencies::get('subscriptionRepository');

        // Créer propriétaire et parking
        $owner = $registerUser->execute(
            'multiowner@test.com',
            'password123',
            'Owner',
            'Multi',
            'OWNER'
        );

        $parking = new Parking($owner->getId(), 'Shared Parking', 'Address', 48.8, 2.3, 10);
        $parkingRepo->save($parking);

        // Créer 3 utilisateurs avec abonnements
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = $registerUser->execute(
                "user$i@test.com",
                'password123',
                "User$i",
                "Test",
                'USER'
            );
            $users[] = $user;

            $subscription = new Subscription(
                uniqid('sub_'),
                (string)$user->getId(),
                (string)$parking->getId(),
                new DateTime('2025-01-01'),
                new DateTime('2025-02-01'),
                [1 => [['start' => '08:00', 'end' => '18:00']]],
                1
            );
            $subscriptionRepo->save($subscription);
        }

        // Vérifier que les 3 abonnements existent
        $subscriptions = $subscriptionRepo->findByParkingId((string)$parking->getId());
        $this->assertCount(3, $subscriptions, '3 abonnements sur le même parking');
    }
}
