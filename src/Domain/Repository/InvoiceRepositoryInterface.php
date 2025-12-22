<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Invoice;

interface InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void;
    public function findById(string $id): ?Invoice;
    public function findByReservationId(string $reservationId): ?Invoice;
}
