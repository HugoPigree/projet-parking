<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Domain\Service\AvailabilityService;

class AvailabilityServiceTest extends TestCase {
    public function testIsParkingBookablePlaceholder() {
        $service = new AvailabilityService();
        $this->assertTrue(method_exists($service, 'isParkingBookable'));
    }
}
