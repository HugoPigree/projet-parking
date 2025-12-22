<?php

namespace App\Tests\Unit;

use App\Domain\Service\PricingService;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Tests unitaires pour le calcul des pénalités
 * Règle métier: +20€ fixe + temps supplémentaire facturé par tranche de 15 min
 */
class PenaltyCalculationTest extends TestCase
{
    private PricingService $pricingService;

    protected function setUp(): void
    {
        $this->pricingService = new PricingService();
    }

    /**
     * Test: Aucun dépassement = aucune pénalité
     */
    public function testNoPenaltyWhenNoOvertime(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 13:45:00'); // Sorti en avance
        $pricePerHour = 5.0;

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        $this->assertEquals(0.0, $penalty, 'Aucune pénalité si sortie avant la fin prévue');
    }

    /**
     * Test: Dépassement exact (fin à l'heure) = aucune pénalité
     */
    public function testNoPenaltyWhenExitAtPlannedTime(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 14:00:00'); // Exactement à l'heure
        $pricePerHour = 5.0;

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        $this->assertEquals(0.0, $penalty, 'Aucune pénalité si sortie exactement à l\'heure prévue');
    }

    /**
     * Test: Dépassement de 1 minute = pénalité minimale (20€ + 1 intervalle)
     */
    public function testPenaltyForOneMinuteOvertime(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 14:01:00'); // 1 minute de retard
        $pricePerHour = 5.0;

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        // 20€ fixe + 1 intervalle de 15 min (5€/4 = 1.25€)
        $expected = 20.0 + (5.0 / 4);
        $this->assertEquals($expected, $penalty, 'Pénalité minimale: 20€ + 1 intervalle');
    }

    /**
     * Test: Dépassement de 30 minutes = 20€ + 2 intervalles
     */
    public function testPenaltyForThirtyMinutesOvertime(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 14:30:00'); // 30 minutes de retard
        $pricePerHour = 5.0;

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        // 20€ fixe + 2 intervalles (30 min = 2 tranches de 15 min)
        // 2 * (5€/4) = 2.50€
        $expected = 20.0 + (2 * (5.0 / 4));
        $this->assertEquals($expected, $penalty, 'Pénalité pour 30 min: 20€ + 2 intervalles');
    }

    /**
     * Test: Dépassement de 1 heure = 20€ + 4 intervalles
     */
    public function testPenaltyForOneHourOvertime(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 15:00:00'); // 1 heure de retard
        $pricePerHour = 5.0;

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        // 20€ fixe + 4 intervalles (1h = 4 tranches de 15 min)
        // 4 * (5€/4) = 5€
        $expected = 20.0 + 5.0;
        $this->assertEquals(25.0, $penalty, 'Pénalité pour 1h: 20€ + tarif horaire complet');
    }

    /**
     * Test: Dépassement de 2h15 = 20€ + 9 intervalles
     */
    public function testPenaltyForTwoHoursFifteenMinutesOvertime(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 16:15:00'); // 2h15 de retard
        $pricePerHour = 5.0;

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        // 20€ fixe + 9 intervalles (135 min = 9 tranches de 15 min)
        // 9 * (5€/4) = 11.25€
        $expected = 20.0 + (9 * (5.0 / 4));
        $this->assertEquals($expected, $penalty, 'Pénalité pour 2h15: 20€ + 9 intervalles');
    }

    /**
     * Test: Tarif horaire différent (10€/h)
     */
    public function testPenaltyWithDifferentHourlyRate(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 15:00:00'); // 1 heure de retard
        $pricePerHour = 10.0; // Tarif plus élevé

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        // 20€ fixe + 4 intervalles (1h = 4 tranches de 15 min)
        // 4 * (10€/4) = 10€
        $expected = 20.0 + 10.0;
        $this->assertEquals(30.0, $penalty, 'Pénalité avec tarif 10€/h: 20€ + 10€');
    }

    /**
     * Test: Arrondi au supérieur pour intervalles incomplets
     */
    public function testPenaltyRoundsUpIntervals(): void
    {
        $plannedEnd = new DateTime('2025-01-15 14:00:00');
        $actualEnd = new DateTime('2025-01-15 14:22:00'); // 22 minutes = 2 intervalles arrondis
        $pricePerHour = 5.0;

        $penalty = $this->pricingService->calculatePenalty($plannedEnd, $actualEnd, $pricePerHour);

        // 22 min => arrondi à 2 intervalles (ceil(22/15) = 2)
        // 20€ fixe + 2 * (5€/4) = 20€ + 2.50€
        $expected = 20.0 + (2 * (5.0 / 4));
        $this->assertEquals($expected, $penalty, 'Les intervalles sont arrondis au supérieur');
    }

    /**
     * Test: Calcul du nombre d'intervalles de 15 minutes
     */
    public function testCalculate15MinIntervals(): void
    {
        $this->assertEquals(0, $this->pricingService->calculate15MinIntervals(0));
        $this->assertEquals(1, $this->pricingService->calculate15MinIntervals(1));
        $this->assertEquals(1, $this->pricingService->calculate15MinIntervals(15));
        $this->assertEquals(2, $this->pricingService->calculate15MinIntervals(16));
        $this->assertEquals(2, $this->pricingService->calculate15MinIntervals(30));
        $this->assertEquals(3, $this->pricingService->calculate15MinIntervals(31));
        $this->assertEquals(4, $this->pricingService->calculate15MinIntervals(60));
    }
}
