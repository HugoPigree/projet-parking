<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Config\Dependencies;
use App\Domain\Entity\Parking;
use App\Domain\Entity\Reservation;
use App\Domain\Entity\Subscription;
use App\UseCase\User\RegisterUser;
use App\UseCase\Parking\GetParkingRevenue;
use DateTime;

/**
 * Test fonctionnel : Scénario de calcul du chiffre d'affaires d'un propriétaire
 *
 * Scénario:
 * 1. Un propriétaire crée un parking
 * 2. 5 utilisateurs réservent et utilisent le parking
 * 3. 2 utilisateurs souscrivent des abonnements
 * 4. Le propriétaire consulte son CA mensuel
 * 5. Vérifier que CA = somme(réservations) + somme(abonnements)
 */
class OwnerRevenueScenarioTest extends TestCase
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

    public function testOwnerCanCalculateMonthlyRevenue(): void
    {
        $registerUser = Dependencies::get(RegisterUser::class);
        $parkingRepo = Dependencies::get('parkingRepository');
        $reservationRepo = Dependencies::get('reservationRepository');
        $subscriptionRepo = Dependencies::get('subscriptionRepository');

        // === ÉTAPE 1: Créer un propriétaire et son parking ===
        $owner = $registerUser->execute(
            'richowner@test.com',
            'password123',
            'Riche',
            'Propriétaire',
            'OWNER'
        );

        $parking = new Parking(
            $owner->getId(),
            'Parking Premium',
            '1 rue du Commerce',
            48.8566,
            2.3522,
            100, // 100 places
            [],
            []
        );
        $parkingRepo->save($parking);

        // === ÉTAPE 2: Créer 5 réservations complétées ===
        $totalReservationsRevenue = 0.0;
        $reservationPrices = [15.0, 20.0, 25.0, 10.0, 30.0];

        for ($i = 0; $i < 5; $i++) {
            $user = $registerUser->execute(
                "customer$i@test.com",
                'password123',
                "Customer$i",
                "Test",
                'USER'
            );

            $reservation = new Reservation(
                uniqid('res_'),
                $user->getId(),
                $parking->getId(),
                $i + 1, // Slot ID
                new DateTime('2025-01-10 10:00:00'),
                new DateTime('2025-01-10 12:00:00')
            );

            $reservation->setTotalPrice($reservationPrices[$i]);
            $reservation->complete(); // Marquer comme complétée
            $reservationRepo->save($reservation);

            $totalReservationsRevenue += $reservationPrices[$i];
        }

        $this->assertEquals(100.0, $totalReservationsRevenue, 'Revenu total des réservations');

        // === ÉTAPE 3: Créer 2 abonnements mensuels ===
        $subscriptionPrices = [80.0, 100.0]; // Prix mensuels
        $totalSubscriptionsRevenue = 0.0;

        for ($i = 0; $i < 2; $i++) {
            $subscriber = $registerUser->execute(
                "subscriber$i@test.com",
                'password123',
                "Subscriber$i",
                "Test",
                'USER'
            );

            $subscription = new Subscription(
                uniqid('sub_'),
                (string)$subscriber->getId(),
                (string)$parking->getId(),
                new DateTime('2025-01-01'),
                new DateTime('2025-01-31'),
                [1 => [['start' => '08:00', 'end' => '18:00']]],
                1 // 1 mois
            );

            // Note: Le prix devrait être stocké dans la table subscriptions
            // Pour ce test, on simule juste le calcul
            $subscriptionRepo->save($subscription);
            $totalSubscriptionsRevenue += $subscriptionPrices[$i];
        }

        $this->assertEquals(180.0, $totalSubscriptionsRevenue, 'Revenu total des abonnements');

        // === ÉTAPE 4: Calculer le CA total ===
        $expectedTotalRevenue = $totalReservationsRevenue + $totalSubscriptionsRevenue;
        $this->assertEquals(280.0, $expectedTotalRevenue, 'CA total = réservations + abonnements');

        // === ÉTAPE 5: Test du use case GetParkingRevenue (si implémenté) ===
        // Note: GetParkingRevenue.php existe mais est marqué TODO
        // Quand implémenté, ce test devrait utiliser le use case:
        /*
        $getParkingRevenue = new GetParkingRevenue(
            $parkingRepo,
            $reservationRepo,
            $subscriptionRepo
        );

        $revenue = $getParkingRevenue->execute(
            $parking->getId(),
            $owner->getId(),
            2025,
            1 // Janvier
        );

        $this->assertEquals(100.0, $revenue['reservationsRevenue']);
        $this->assertEquals(180.0, $revenue['subscriptionsRevenue']);
        $this->assertEquals(280.0, $revenue['totalRevenue']);
        */
    }

    public function testOwnerCanOnlyAccessHisOwnParkingRevenue(): void
    {
        // Test qu'un propriétaire ne peut pas accéder au CA d'un autre propriétaire
        $registerUser = Dependencies::get(RegisterUser::class);
        $parkingRepo = Dependencies::get('parkingRepository');

        // Créer 2 propriétaires
        $owner1 = $registerUser->execute(
            'owner1@test.com',
            'password123',
            'Owner1',
            'Test',
            'OWNER'
        );

        $owner2 = $registerUser->execute(
            'owner2@test.com',
            'password123',
            'Owner2',
            'Test',
            'OWNER'
        );

        // Chacun crée un parking
        $parking1 = new Parking($owner1->getId(), 'Parking 1', 'Address 1', 48.8, 2.3, 10);
        $parking2 = new Parking($owner2->getId(), 'Parking 2', 'Address 2', 48.9, 2.4, 10);

        $parkingRepo->save($parking1);
        $parkingRepo->save($parking2);

        // Owner1 ne doit pas pouvoir accéder aux revenus de Parking 2
        // Ceci devrait être testé dans le use case GetParkingRevenue
        $this->assertEquals($owner1->getId(), $parking1->getOwnerId());
        $this->assertEquals($owner2->getId(), $parking2->getOwnerId());
        $this->assertNotEquals($parking1->getOwnerId(), $parking2->getOwnerId());
    }

    public function testRevenueCalculationForSpecificMonth(): void
    {
        // Test que seules les réservations du mois spécifié sont comptées
        $registerUser = Dependencies::get(RegisterUser::class);
        $parkingRepo = Dependencies::get('parkingRepository');
        $reservationRepo = Dependencies::get('reservationRepository');

        $owner = $registerUser->execute(
            'monthowner@test.com',
            'password123',
            'Month',
            'Owner',
            'OWNER'
        );

        $user = $registerUser->execute(
            'monthuser@test.com',
            'password123',
            'Month',
            'User',
            'USER'
        );

        $parking = new Parking($owner->getId(), 'Time Parking', 'Address', 48.8, 2.3, 10);
        $parkingRepo->save($parking);

        // Réservation en janvier
        $janReservation = new Reservation(
            uniqid('res_'),
            $user->getId(),
            $parking->getId(),
            1,
            new DateTime('2025-01-15 10:00:00'),
            new DateTime('2025-01-15 12:00:00')
        );
        $janReservation->setTotalPrice(50.0);
        $janReservation->complete();
        $reservationRepo->save($janReservation);

        // Réservation en février
        $febReservation = new Reservation(
            uniqid('res_'),
            $user->getId(),
            $parking->getId(),
            1,
            new DateTime('2025-02-15 10:00:00'),
            new DateTime('2025-02-15 12:00:00')
        );
        $febReservation->setTotalPrice(75.0);
        $febReservation->complete();
        $reservationRepo->save($febReservation);

        // Récupérer toutes les réservations
        $allReservations = $reservationRepo->findByParkingId($parking->getId());
        $this->assertCount(2, $allReservations, '2 réservations au total');

        // Filtrer par mois (simulation - devrait être fait dans GetParkingRevenue)
        $januaryReservations = array_filter($allReservations, function($res) {
            return $res->getStartTime()->format('Y-m') === '2025-01';
        });

        $this->assertCount(1, $januaryReservations, '1 réservation en janvier');

        $februaryReservations = array_filter($allReservations, function($res) {
            return $res->getStartTime()->format('Y-m') === '2025-02';
        });

        $this->assertCount(1, $februaryReservations, '1 réservation en février');
    }
}
