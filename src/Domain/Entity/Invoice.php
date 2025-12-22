<?php
namespace App\Domain\Entity;

use DateTime;

class Invoice
{
    private ?int $id = null;
    private string $invoiceNumber;
    private int $reservationId;
    private int $userId;

    private float $subtotal = 0.0;
    private float $penalty = 0.0;
    private float $taxRate = 0.0;
    private float $taxAmount = 0.0;
    private float $totalAmount = 0.0;

    private string $currency = 'XOF'; 
    private string $status = 'UNPAID';

    private DateTime $issueDate;
    private DateTime $dueDate;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private array $items = [];

    public function __construct(
        string $invoiceNumber,
        int $reservationId,
        int $userId
    ) {
        $this->invoiceNumber = $invoiceNumber;
        $this->reservationId = $reservationId;
        $this->userId = $userId;

        $this->issueDate = new DateTime();
        $this->dueDate = (new DateTime())->modify('+7 days'); 
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getInvoiceNumber(): string { return $this->invoiceNumber; }
    public function getReservationId(): int { return $this->reservationId; }
    public function getUserId(): int { return $this->userId; }

    public function getSubtotal(): float { return $this->subtotal; }
    public function getPenalty(): float { return $this->penalty; }
    public function setPenalty(float $penalty): void
    {
        $this->penalty = $penalty;
        $this->recalculate();
    }
    public function getTaxRate(): float { return $this->taxRate; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getTotalAmount(): float { return $this->totalAmount; }

    public function getCurrency(): string { return $this->currency; }
    public function getStatus(): string { return $this->status; }

    public function getIssueDate(): DateTime { return $this->issueDate; }
    public function getDueDate(): DateTime { return $this->dueDate; }

    public function setSubtotal(float $subtotal): void
    {
        $this->subtotal = $subtotal;
        $this->recalculate();
    }

    public function setTaxRate(float $rate): void
    {
        $this->taxRate = $rate;
        $this->recalculate();
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Ajoute une ligne à la facture
     */
    public function addItem(string $label, int $qty, float $unitPrice): void
    {
        $total = $qty * $unitPrice;

        $this->items[] = [
            'label' => $label,
            'qty' => $qty,
            'unit' => $unitPrice,
            'total' => $total
        ];

        $this->recalculateFromItems();
    }

    /**
     * Obtient le montant total (incluant pénalités si applicable)
     */
    public function getTotal(): float
    {
        return $this->totalAmount;
    }

    private function recalculate(): void
    {
        $this->taxAmount = ($this->subtotal * $this->taxRate) / 100;
        // Total = Sous-total + Taxes + Pénalités
        $this->totalAmount = $this->subtotal + $this->taxAmount + $this->penalty;
    }

    /**
     * Recalcule le total à partir des items
     */
    private function recalculateFromItems(): void
    {
        $this->subtotal = array_sum(array_column($this->items, 'total'));
        $this->recalculate();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoiceNumber,
            'reservation_id' => $this->reservationId,
            'user_id' => $this->userId,

            'subtotal' => $this->subtotal,
            'tax_rate' => $this->taxRate,
            'tax_amount' => $this->taxAmount,
            'total_amount' => $this->totalAmount,

            'currency' => $this->currency,
            'status' => $this->status,

            'issue_date' => $this->issueDate->format('Y-m-d H:i:s'),
            'due_date' => $this->dueDate->format('Y-m-d H:i:s'),
        ];

        
    }
    public function getItems(): array
{
    return $this->items;
}
public function getAmount(): float
{
    return $this->totalAmount;
}


public function getGeneratedAt(): \DateTime
{
    return $this->createdAt;
}

}

