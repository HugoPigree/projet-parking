<?php

namespace App\Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Parking;

class ParkingTest extends TestCase
{
    private Parking $parking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->parking = new Parking(
            ownerId: 1,
            name: 'Parking Central',
            address: '123 Rue de la Paix, Paris',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 50,
            openingHours: [],
            pricingRules: [],
            id: null
        );
    }

    // Tests du constructeur
    public function testConstructorInitializesAllProperties(): void
    {
        $parking = new Parking(
            ownerId: 2,
            name: 'Test Parking',
            address: 'Test Address',
            latitude: 45.0,
            longitude: 3.0,
            totalSpots: 20
        );

        $this->assertNull($parking->getId());
        $this->assertEquals(2, $parking->getOwnerId());
        $this->assertEquals('Test Parking', $parking->getName());
        $this->assertEquals('Test Address', $parking->getAddress());
        $this->assertEquals(45.0, $parking->getLatitude());
        $this->assertEquals(3.0, $parking->getLongitude());
        $this->assertEquals(20, $parking->getTotalSpots());
        $this->assertIsArray($parking->getOpeningHours());
        $this->assertIsArray($parking->getPricingRules());
        $this->assertIsArray($parking->getReservationIds());
        $this->assertIsArray($parking->getStationnementIds());
        $this->assertInstanceOf(\DateTime::class, $parking->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $parking->getUpdatedAt());
    }

    public function testConstructorWithId(): void
    {
        $parking = new Parking(
            ownerId: 1,
            name: 'Test',
            address: 'Address',
            latitude: 48.0,
            longitude: 2.0,
            totalSpots: 10,
            openingHours: [],
            pricingRules: [],
            id: 42
        );

        $this->assertEquals(42, $parking->getId());
    }

    public function testConstructorWithOpeningHoursAndPricingRules(): void
    {
        $openingHours = [
            ['day' => 1, 'start' => '08:00', 'end' => '18:00'],
            ['day' => 2, 'start' => '08:00', 'end' => '18:00']
        ];
        $pricingRules = [
            ['intervalMinutes' => 15, 'pricePerInterval' => 2.5]
        ];

        $parking = new Parking(
            ownerId: 1,
            name: 'Test',
            address: 'Address',
            latitude: 48.0,
            longitude: 2.0,
            totalSpots: 10,
            openingHours: $openingHours,
            pricingRules: $pricingRules
        );

        $this->assertEquals($openingHours, $parking->getOpeningHours());
        $this->assertEquals($pricingRules, $parking->getPricingRules());
    }

    // Tests des getters
    public function testGetId(): void
    {
        $this->assertNull($this->parking->getId());
        
        $this->parking->setId(5);
        $this->assertEquals(5, $this->parking->getId());
    }

    public function testGetOwnerId(): void
    {
        $this->assertEquals(1, $this->parking->getOwnerId());
    }

    public function testGetName(): void
    {
        $this->assertEquals('Parking Central', $this->parking->getName());
    }

    public function testGetAddress(): void
    {
        $this->assertEquals('123 Rue de la Paix, Paris', $this->parking->getAddress());
    }

    public function testGetLatitude(): void
    {
        $this->assertEquals(48.8566, $this->parking->getLatitude());
    }

    public function testGetLongitude(): void
    {
        $this->assertEquals(2.3522, $this->parking->getLongitude());
    }

    public function testGetTotalSpots(): void
    {
        $this->assertEquals(50, $this->parking->getTotalSpots());
    }

    public function testGetOpeningHours(): void
    {
        $this->assertIsArray($this->parking->getOpeningHours());
        $this->assertEmpty($this->parking->getOpeningHours());
    }

    public function testGetPricingRules(): void
    {
        $this->assertIsArray($this->parking->getPricingRules());
        $this->assertEmpty($this->parking->getPricingRules());
    }

    public function testGetReservationIds(): void
    {
        $this->assertIsArray($this->parking->getReservationIds());
        $this->assertEmpty($this->parking->getReservationIds());
    }

    public function testGetStationnementIds(): void
    {
        $this->assertIsArray($this->parking->getStationnementIds());
        $this->assertEmpty($this->parking->getStationnementIds());
    }

    public function testGetCreatedAt(): void
    {
        $this->assertInstanceOf(\DateTime::class, $this->parking->getCreatedAt());
    }

    public function testGetUpdatedAt(): void
    {
        $this->assertInstanceOf(\DateTime::class, $this->parking->getUpdatedAt());
    }

    // Tests des setters
    public function testSetId(): void
    {
        $this->parking->setId(10);
        $this->assertEquals(10, $this->parking->getId());
    }

    public function testSetOwnerIdUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1); // Attendre 1 seconde pour que le timestamp change
        
        $this->parking->setOwnerId(99);
        
        $this->assertEquals(99, $this->parking->getOwnerId());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetNameUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->setName('Nouveau Nom');
        
        $this->assertEquals('Nouveau Nom', $this->parking->getName());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetAddressUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->setAddress('Nouvelle Adresse');
        
        $this->assertEquals('Nouvelle Adresse', $this->parking->getAddress());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetLatitudeUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->setLatitude(49.0);
        
        $this->assertEquals(49.0, $this->parking->getLatitude());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetLongitudeUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->setLongitude(3.0);
        
        $this->assertEquals(3.0, $this->parking->getLongitude());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetTotalSpotsUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->setTotalSpots(100);
        
        $this->assertEquals(100, $this->parking->getTotalSpots());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetOpeningHoursUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $newHours = [['day' => 1, 'start' => '09:00', 'end' => '17:00']];
        $this->parking->setOpeningHours($newHours);
        
        $this->assertEquals($newHours, $this->parking->getOpeningHours());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetPricingRulesUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $newRules = [['intervalMinutes' => 30, 'pricePerInterval' => 5.0]];
        $this->parking->setPricingRules($newRules);
        
        $this->assertEquals($newRules, $this->parking->getPricingRules());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetReservationIdsUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->setReservationIds([1, 2, 3]);
        
        $this->assertEquals([1, 2, 3], $this->parking->getReservationIds());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testSetStationnementIdsUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->setStationnementIds([10, 20]);
        
        $this->assertEquals([10, 20], $this->parking->getStationnementIds());
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    // Tests de hasAvailableSpot()
    public function testHasAvailableSpotReturnsTrueWhenSpotsAvailable(): void
    {
        $this->assertTrue($this->parking->hasAvailableSpot(0));
        $this->assertTrue($this->parking->hasAvailableSpot(25));
        $this->assertTrue($this->parking->hasAvailableSpot(49));
    }

    public function testHasAvailableSpotReturnsFalseWhenFull(): void
    {
        $this->assertFalse($this->parking->hasAvailableSpot(50));
        $this->assertFalse($this->parking->hasAvailableSpot(100));
    }

    // Tests de isOpenAt()
    public function testIsOpenAtReturnsTrueWhenNoOpeningHours(): void
    {
        $timestamp = new \DateTime('2024-01-15 10:00:00');
        $this->assertTrue($this->parking->isOpenAt($timestamp));
    }

    public function testIsOpenAtReturnsTrueForValidTimeSlot(): void
    {
        $this->parking->setOpeningHours([
            ['day' => 1, 'start' => '08:00', 'end' => '18:00']
        ]);

        $monday = new \DateTime('2024-01-15 10:00:00'); // Lundi
        $this->assertTrue($this->parking->isOpenAt($monday));
    }

    public function testIsOpenAtReturnsFalseForTimeOutsideSchedule(): void
    {
        $this->parking->setOpeningHours([
            ['day' => 1, 'start' => '08:00', 'end' => '18:00']
        ]);

        $monday = new \DateTime('2024-01-15 20:00:00'); // Lundi 20h
        $this->assertFalse($this->parking->isOpenAt($monday));
    }

    public function testIsOpenAtReturnsFalseForWrongDay(): void
    {
        $this->parking->setOpeningHours([
            ['day' => 1, 'start' => '08:00', 'end' => '18:00'] // Lundi seulement
        ]);

        $tuesday = new \DateTime('2024-01-16 10:00:00'); // Mardi
        $this->assertFalse($this->parking->isOpenAt($tuesday));
    }

    public function testIsOpenAtHandlesOvernightSchedule(): void
    {
        $this->parking->setOpeningHours([
            ['start' => '18:00', 'end' => '08:00'] // 18h à 8h du lendemain
        ]);

        $evening = new \DateTime('2024-01-15 20:00:00');
        $night = new \DateTime('2024-01-16 02:00:00');
        $morning = new \DateTime('2024-01-16 07:00:00');
        $afternoon = new \DateTime('2024-01-16 12:00:00');

        $this->assertTrue($this->parking->isOpenAt($evening));
        $this->assertTrue($this->parking->isOpenAt($night));
        $this->assertTrue($this->parking->isOpenAt($morning));
        $this->assertFalse($this->parking->isOpenAt($afternoon));
    }

    public function testIsOpenAtHandlesMultipleSchedules(): void
    {
        $this->parking->setOpeningHours([
            ['day' => 1, 'start' => '08:00', 'end' => '12:00'],
            ['day' => 1, 'start' => '14:00', 'end' => '18:00']
        ]);

        $morning = new \DateTime('2024-01-15 10:00:00');
        $afternoon = new \DateTime('2024-01-15 16:00:00');
        $lunch = new \DateTime('2024-01-15 13:00:00');

        $this->assertTrue($this->parking->isOpenAt($morning));
        $this->assertTrue($this->parking->isOpenAt($afternoon));
        $this->assertFalse($this->parking->isOpenAt($lunch));
    }

    public function testIsOpenAtHandlesScheduleWithoutDay(): void
    {
        $this->parking->setOpeningHours([
            ['start' => '09:00', 'end' => '17:00'] // Tous les jours
        ]);

        $monday = new \DateTime('2024-01-15 10:00:00');
        $tuesday = new \DateTime('2024-01-16 10:00:00');
        $sunday = new \DateTime('2024-01-21 10:00:00');

        $this->assertTrue($this->parking->isOpenAt($monday));
        $this->assertTrue($this->parking->isOpenAt($tuesday));
        $this->assertTrue($this->parking->isOpenAt($sunday));
    }

    // Tests de getAvailableSpots()
    public function testGetAvailableSpotsReturnsCorrectNumber(): void
    {
        $this->assertEquals(50, $this->parking->getAvailableSpots(0));
        $this->assertEquals(25, $this->parking->getAvailableSpots(25));
        $this->assertEquals(0, $this->parking->getAvailableSpots(50));
    }

    public function testGetAvailableSpotsReturnsZeroWhenOverCapacity(): void
    {
        $this->assertEquals(0, $this->parking->getAvailableSpots(100));
        $this->assertEquals(0, $this->parking->getAvailableSpots(200));
    }

    // Tests de addReservationId() et removeReservationId()
    public function testAddReservationId(): void
    {
        $this->parking->addReservationId(1);
        $this->assertContains(1, $this->parking->getReservationIds());
    }

    public function testAddReservationIdDoesNotDuplicate(): void
    {
        $this->parking->addReservationId(1);
        $this->parking->addReservationId(1);
        
        $reservations = $this->parking->getReservationIds();
        $this->assertEquals(1, count($reservations));
        $this->assertEquals(1, $reservations[0]);
    }

    public function testAddReservationIdUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->addReservationId(5);
        
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testRemoveReservationId(): void
    {
        $this->parking->addReservationId(1);
        $this->parking->addReservationId(2);
        $this->parking->addReservationId(3);
        
        $this->parking->removeReservationId(2);
        
        $reservations = $this->parking->getReservationIds();
        $this->assertNotContains(2, $reservations);
        $this->assertContains(1, $reservations);
        $this->assertContains(3, $reservations);
    }

    public function testRemoveReservationIdDoesNothingIfNotExists(): void
    {
        $this->parking->addReservationId(1);
        $this->parking->removeReservationId(999);
        
        $this->assertContains(1, $this->parking->getReservationIds());
    }

    public function testRemoveReservationIdUpdatesTimestamp(): void
    {
        $this->parking->addReservationId(1);
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->removeReservationId(1);
        
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    // Tests de addStationnementId() et removeStationnementId()
    public function testAddStationnementId(): void
    {
        $this->parking->addStationnementId(10);
        $this->assertContains(10, $this->parking->getStationnementIds());
    }

    public function testAddStationnementIdDoesNotDuplicate(): void
    {
        $this->parking->addStationnementId(10);
        $this->parking->addStationnementId(10);
        
        $stationnements = $this->parking->getStationnementIds();
        $this->assertEquals(1, count($stationnements));
        $this->assertEquals(10, $stationnements[0]);
    }

    public function testAddStationnementIdUpdatesTimestamp(): void
    {
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->addStationnementId(5);
        
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    public function testRemoveStationnementId(): void
    {
        $this->parking->addStationnementId(10);
        $this->parking->addStationnementId(20);
        $this->parking->addStationnementId(30);
        
        $this->parking->removeStationnementId(20);
        
        $stationnements = $this->parking->getStationnementIds();
        $this->assertNotContains(20, $stationnements);
        $this->assertContains(10, $stationnements);
        $this->assertContains(30, $stationnements);
    }

    public function testRemoveStationnementIdDoesNothingIfNotExists(): void
    {
        $this->parking->addStationnementId(10);
        $this->parking->removeStationnementId(999);
        
        $this->assertContains(10, $this->parking->getStationnementIds());
    }

    public function testRemoveStationnementIdUpdatesTimestamp(): void
    {
        $this->parking->addStationnementId(10);
        $oldUpdatedAt = $this->parking->getUpdatedAt();
        sleep(1);
        
        $this->parking->removeStationnementId(10);
        
        $this->assertGreaterThan($oldUpdatedAt, $this->parking->getUpdatedAt());
    }

    // Tests de calculateDistance()
    public function testCalculateDistanceReturnsZeroForSameLocation(): void
    {
        $distance = $this->parking->calculateDistance(48.8566, 2.3522);
        $this->assertEquals(0.0, $distance, 'Distance should be 0 for same coordinates', 0.1);
    }

    public function testCalculateDistanceReturnsCorrectDistance(): void
    {
        // Distance approximative entre Paris et Lyon
        $parisLat = 48.8566;
        $parisLon = 2.3522;
        $lyonLat = 45.7640;
        $lyonLon = 4.8357;
        
        $parking = new Parking(
            ownerId: 1,
            name: 'Paris Parking',
            address: 'Paris',
            latitude: $parisLat,
            longitude: $parisLon,
            totalSpots: 10
        );
        
        $distance = $parking->calculateDistance($lyonLat, $lyonLon);
        
        // Distance réelle Paris-Lyon ≈ 392 km
        $this->assertGreaterThan(350, $distance);
        $this->assertLessThan(450, $distance);
    }

    public function testCalculateDistanceWithSmallDistance(): void
    {
        // Distance très courte (quelques mètres)
        $distance = $this->parking->calculateDistance(48.8567, 2.3523);
        
        // Devrait être très petit (moins de 1 km)
        $this->assertLessThan(1.0, $distance);
        $this->assertGreaterThan(0.0, $distance);
    }

    public function testCalculateDistanceIsSymmetric(): void
    {
        $lat1 = 48.8566;
        $lon1 = 2.3522;
        $lat2 = 45.7640;
        $lon2 = 4.8357;
        
        $parking1 = new Parking(1, 'Parking 1', 'Address', $lat1, $lon1, 10);
        $parking2 = new Parking(1, 'Parking 2', 'Address', $lat2, $lon2, 10);
        
        $distance1 = $parking1->calculateDistance($lat2, $lon2);
        $distance2 = $parking2->calculateDistance($lat1, $lon1);
        
        $this->assertEquals($distance1, $distance2, 'Distance should be symmetric', 0.1);
    }
}

