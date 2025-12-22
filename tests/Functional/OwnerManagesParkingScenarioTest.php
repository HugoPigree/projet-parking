<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\UseCase\Parking\CreateParking;
use App\UseCase\Parking\UpdateParking;
use App\UseCase\Parking\GetParkingAvailability;
use App\UseCase\Parking\GetParkingRevenue;
use App\Infrastructure\InMemory\InMemoryParkingRepository;
use App\Domain\Service\AvailabilityService;

/**
 * Test fonctionnel : Scénario complet de gestion d'un parking par un propriétaire
 */
class OwnerManagesParkingScenarioTest extends TestCase
{
    private InMemoryParkingRepository $repository;
    private CreateParking $createParking;
    private UpdateParking $updateParking;
    private GetParkingAvailability $getAvailability;
    private GetParkingRevenue $getRevenue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryParkingRepository();
        $availabilityService = new AvailabilityService();
        
        $this->createParking = new CreateParking($this->repository);
        $this->updateParking = new UpdateParking($this->repository);
        $this->getAvailability = new GetParkingAvailability($this->repository, $availabilityService);
        $this->getRevenue = new GetParkingRevenue($this->repository);
    }

    public function testOwnerCanCreateAndManageParking(): void
    {
        $ownerId = 1;

        // 1. Créer un parking
        $parking = $this->createParking->execute(
            ownerId: $ownerId,
            name: 'Parking Central',
            address: '123 Rue de la Paix, Paris',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 50,
            openingHours: [
                ['day' => 1, 'start' => '08:00', 'end' => '18:00'],
                ['day' => 2, 'start' => '08:00', 'end' => '18:00']
            ],
            pricingRules: [
                ['intervalMinutes' => 15, 'pricePerInterval' => 2.5]
            ]
        );

        $this->assertNotNull($parking->getId());
        $this->assertEquals('Parking Central', $parking->getName());
        $this->assertEquals(50, $parking->getTotalSpots());

        // 2. Modifier les horaires
        $updatedParking = $this->updateParking->execute(
            parkingId: $parking->getId(),
            ownerId: $ownerId,
            data: [
                'openingHours' => [
                    ['day' => 1, 'start' => '09:00', 'end' => '19:00']
                ]
            ]
        );

        $this->assertCount(1, $updatedParking->getOpeningHours());

        // 3. Vérifier la disponibilité
        $timestamp = new \DateTime('2024-01-15 10:00:00');
        $availability = $this->getAvailability->execute($parking->getId(), $timestamp);

        $this->assertEquals(50, $availability['totalSpots']);
        $this->assertTrue($availability['isOpen']);

        // 4. Obtenir le chiffre d'affaire (même si vide pour l'instant)
        $revenue = $this->getRevenue->execute($parking->getId(), $ownerId, 2024, 1);

        $this->assertEquals(2024, $revenue['year']);
        $this->assertEquals(1, $revenue['month']);
    }

    public function testOwnerCannotUpdateOtherOwnerParking(): void
    {
        $owner1Id = 1;
        $owner2Id = 2;

        // Créer un parking pour owner1
        $parking = $this->createParking->execute(
            ownerId: $owner1Id,
            name: 'Parking Owner 1',
            address: 'Address',
            latitude: 48.0,
            longitude: 2.0,
            totalSpots: 10
        );

        // Owner2 essaie de modifier le parking d'owner1
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Vous n\'êtes pas propriétaire de ce parking');

        $this->updateParking->execute(
            parkingId: $parking->getId(),
            ownerId: $owner2Id,
            data: ['name' => 'Hacked']
        );
    }
}

