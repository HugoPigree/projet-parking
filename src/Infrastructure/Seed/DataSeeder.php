<?php

namespace App\Infrastructure\Seed;

use App\Domain\Entity\Parking;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Seeder pour créer des données de test
 */
class DataSeeder
{
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(ParkingRepositoryInterface $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Seed les données de base
     */
    public function seed(): void
    {
        $this->seedParkings();
    }

    /**
     * Crée des parkings de test
     */
    private function seedParkings(): void
    {
        // Parking 1 : Paris Centre
        $parking1 = new Parking(
            ownerId: 1,
            name: 'Parking Central Paris',
            address: '123 Rue de Rivoli, 75001 Paris',
            latitude: 48.8606,
            longitude: 2.3376,
            totalSpots: 100,
            openingHours: [
                ['start' => '00:00', 'end' => '23:59'] // Ouvert 24/7
            ],
            pricingRules: [
                ['intervalMinutes' => 15, 'pricePerInterval' => 2.5, 'maxDurationMinutes' => 60],
                ['intervalMinutes' => 15, 'pricePerInterval' => 2.0, 'maxDurationMinutes' => null]
            ]
        );
        $this->parkingRepository->save($parking1);

        // Parking 2 : Paris Nord
        $parking2 = new Parking(
            ownerId: 1,
            name: 'Parking Gare du Nord',
            address: '18 Rue de Dunkerque, 75010 Paris',
            latitude: 48.8809,
            longitude: 2.3553,
            totalSpots: 80,
            openingHours: [
                ['day' => 1, 'start' => '06:00', 'end' => '22:00'],
                ['day' => 2, 'start' => '06:00', 'end' => '22:00'],
                ['day' => 3, 'start' => '06:00', 'end' => '22:00'],
                ['day' => 4, 'start' => '06:00', 'end' => '22:00'],
                ['day' => 5, 'start' => '06:00', 'end' => '22:00']
            ],
            pricingRules: [
                ['intervalMinutes' => 15, 'pricePerInterval' => 3.0]
            ]
        );
        $this->parkingRepository->save($parking2);

        // Parking 3 : Week-end uniquement
        $parking3 = new Parking(
            ownerId: 2,
            name: 'Parking Week-end',
            address: '45 Avenue des Champs-Élysées, 75008 Paris',
            latitude: 48.8698,
            longitude: 2.3081,
            totalSpots: 50,
            openingHours: [
                ['day' => 5, 'start' => '18:00', 'end' => '23:59'],
                ['day' => 6, 'start' => '00:00', 'end' => '23:59'],
                ['day' => 7, 'start' => '00:00', 'end' => '10:00']
            ],
            pricingRules: [
                ['intervalMinutes' => 15, 'pricePerInterval' => 4.0]
            ]
        );
        $this->parkingRepository->save($parking3);

        // Parking 4 : Soir uniquement
        $parking4 = new Parking(
            ownerId: 2,
            name: 'Parking Soirée',
            address: '78 Boulevard Saint-Germain, 75006 Paris',
            latitude: 48.8534,
            longitude: 2.3488,
            totalSpots: 30,
            openingHours: [
                ['start' => '18:00', 'end' => '08:00'] // 18h à 8h du lendemain
            ],
            pricingRules: [
                ['intervalMinutes' => 15, 'pricePerInterval' => 2.0]
            ]
        );
        $this->parkingRepository->save($parking4);
    }

    /**
     * Vide toutes les données
     */
    public function clear(): void
    {
        // Pour InMemory, on peut vider directement
        if (method_exists($this->parkingRepository, 'clear')) {
            $this->parkingRepository->clear();
        }
    }
}

