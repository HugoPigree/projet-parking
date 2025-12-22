<?php

namespace App\Tests\Unit;

use App\Domain\Entity\Invoice;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la facturation avec pénalités
 */
class InvoiceWithPenaltyTest extends TestCase
{
    /**
     * Test: Facture simple sans pénalité
     */
    public function testInvoiceWithoutPenalty(): void
    {
        $invoice = new Invoice('INV-001', 1, 1);
        $invoice->addItem('Stationnement 2h', 8, 1.25); // 8 intervalles de 15 min à 1.25€

        $this->assertEquals(10.0, $invoice->getSubtotal());
        $this->assertEquals(0.0, $invoice->getPenalty());
        $this->assertEquals(10.0, $invoice->getTotal());
    }

    /**
     * Test: Facture avec pénalité
     */
    public function testInvoiceWithPenalty(): void
    {
        $invoice = new Invoice('INV-002', 2, 1);
        $invoice->addItem('Stationnement 2h', 8, 1.25); // 10€
        $invoice->setPenalty(22.50); // 20€ fixe + 2.50€ de surcoût

        $this->assertEquals(10.0, $invoice->getSubtotal());
        $this->assertEquals(22.50, $invoice->getPenalty());
        $this->assertEquals(32.50, $invoice->getTotal(), 'Total = Sous-total + Pénalité');
    }

    /**
     * Test: Facture avec taxes
     */
    public function testInvoiceWithTaxes(): void
    {
        $invoice = new Invoice('INV-003', 3, 1);
        $invoice->addItem('Stationnement', 4, 2.0); // 8€
        $invoice->setTaxRate(20); // TVA 20%

        $this->assertEquals(8.0, $invoice->getSubtotal());
        $this->assertEquals(1.6, $invoice->getTaxAmount());
        $this->assertEquals(9.6, $invoice->getTotal());
    }

    /**
     * Test: Facture avec taxes ET pénalité
     */
    public function testInvoiceWithTaxesAndPenalty(): void
    {
        $invoice = new Invoice('INV-004', 4, 1);
        $invoice->addItem('Stationnement', 4, 2.0); // 8€
        $invoice->setTaxRate(20); // TVA 20% = 1.6€
        $invoice->setPenalty(20.0); // Pénalité fixe

        $this->assertEquals(8.0, $invoice->getSubtotal());
        $this->assertEquals(1.6, $invoice->getTaxAmount());
        $this->assertEquals(20.0, $invoice->getPenalty());
        $this->assertEquals(29.6, $invoice->getTotal(), 'Total = Sous-total + Taxes + Pénalité');
    }

    /**
     * Test: Plusieurs items
     */
    public function testInvoiceWithMultipleItems(): void
    {
        $invoice = new Invoice('INV-005', 5, 1);
        $invoice->addItem('Stationnement base', 4, 2.0); // 8€
        $invoice->addItem('Temps supplémentaire', 2, 1.25); // 2.50€
        $invoice->setPenalty(20.0);

        $this->assertEquals(10.5, $invoice->getSubtotal());
        $this->assertEquals(30.5, $invoice->getTotal());
        $this->assertCount(2, $invoice->getItems());
    }

    /**
     * Test: getTotal() retourne bien le montant total
     */
    public function testGetTotalReturnsCorrectAmount(): void
    {
        $invoice = new Invoice('INV-006', 6, 1);
        $invoice->addItem('Stationnement', 1, 5.0);
        $invoice->setPenalty(25.0);

        $total = $invoice->getTotal();
        $this->assertEquals(30.0, $total);
        $this->assertEquals($invoice->getTotalAmount(), $total);
    }
}
