<?php
namespace App\Infrastructure\InMemory;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Entity\Invoice;

class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
{
    private array $storage = [];

    public function save(Invoice $invoice): void
    {
        $this->storage[$invoice->getId()] = $invoice;
    }

    public function findById(string $id): ?Invoice
    {
        return $this->storage[$id] ?? null;
    }

    public function findByReservationId(string $reservationId): ?Invoice
    {
        foreach ($this->storage as $inv) {
            if ($inv->getReservationId() === $reservationId) {
                return $inv;
            }
        }
        return null;
    }
}
