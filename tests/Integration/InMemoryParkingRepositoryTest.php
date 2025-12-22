<?php

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\InMemory\InMemoryParkingRepository;
use App\Domain\Entity\Parking;

class InMemoryParkingRepositoryTest extends TestCase
{
    private InMemoryParkingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryParkingRepository();
    }

    public function testSaveCreatesNewParkingWithId(): void
    {
        $parking = new Parking(
            ownerId: 1,
            name: 'Test Parking',
            address: 'Test Address',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 50
        );

        $savedParking = $this->repository->save($parking);

        $this->assertNotNull($savedParking->getId());
        $this->assertEquals(1, $savedParking->getId());
    }

    public function testFindByIdReturnsParking(): void
    {
        $parking = new Parking(1, 'Test', 'Address', 48.0, 2.0, 10);
        $this->repository->save($parking);

        $found = $this->repository->findById(1);

        $this->assertNotNull($found);
        $this->assertEquals('Test', $found->getName());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $found = $this->repository->findById(999);
        $this->assertNull($found);
    }

    public function testFindByOwnerReturnsOnlyOwnerParkings(): void
    {
        $parking1 = new Parking(1, 'Parking 1', 'Address', 48.0, 2.0, 10);
        $parking2 = new Parking(1, 'Parking 2', 'Address', 48.0, 2.0, 10);
        $parking3 = new Parking(2, 'Parking 3', 'Address', 48.0, 2.0, 10);

        $this->repository->save($parking1);
        $this->repository->save($parking2);
        $this->repository->save($parking3);

        $ownerParkings = $this->repository->findByOwner(1);

        $this->assertCount(2, $ownerParkings);
        foreach ($ownerParkings as $parking) {
            $this->assertEquals(1, $parking->getOwnerId());
        }
    }

    public function testFindNearbyReturnsParkingsWithinRadius(): void
    {
        // Paris
        $parking1 = new Parking(1, 'Paris Parking', 'Paris', 48.8566, 2.3522, 10);
        // Lyon (environ 392 km de Paris)
        $parking2 = new Parking(1, 'Lyon Parking', 'Lyon', 45.7640, 4.8357, 10);
        // Proche de Paris
        $parking3 = new Parking(1, 'Near Paris', 'Near', 48.8600, 2.3600, 10);

        $this->repository->save($parking1);
        $this->repository->save($parking2);
        $this->repository->save($parking3);

        $nearby = $this->repository->findNearby(48.8566, 2.3522, 10.0);

        $this->assertCount(2, $nearby);
        $this->assertEquals('Paris Parking', $nearby[0]->getName());
    }

    public function testDeleteRemovesParking(): void
    {
        $parking = new Parking(1, 'Test', 'Address', 48.0, 2.0, 10);
        $this->repository->save($parking);

        $result = $this->repository->delete(1);

        $this->assertTrue($result);
        $this->assertNull($this->repository->findById(1));
    }

    public function testExistsReturnsTrueWhenParkingExists(): void
    {
        $parking = new Parking(1, 'Test', 'Address', 48.0, 2.0, 10);
        $this->repository->save($parking);

        $this->assertTrue($this->repository->exists(1));
        $this->assertFalse($this->repository->exists(999));
    }
}

