<?php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Entity\Invoice;
use PDO;
use DateTime;

class PDOInvoiceRepository implements InvoiceRepositoryInterface
{
    private PDO $pdo;
   public function __construct(
        
    ) {
          $config = require __DIR__ . '/../../config/database.php';

        $this->pdo = new PDO(
            $config['dsn'],
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }


    public function save(Invoice $invoice): void
    {
        $itemsJson = json_encode($invoice->getItems(), JSON_THROW_ON_ERROR);

        $exists = $invoice->getId() ? $this->findById($invoice->getId()) : null;

        if ($exists) {
            // UPDATE
            $stmt = $this->pdo->prepare("
                UPDATE invoices
                SET 
                    invoice_number = :invoice_number,
                    reservation_id = :reservation_id,
                    user_id = :user_id,
                    subtotal = :subtotal,
                    tax_rate = :tax_rate,
                    tax_amount = :tax_amount,
                    total_amount = :total_amount,
                    currency = :currency,
                    status = :status,
                    issue_date = :issue_date,
                    due_date = :due_date,
                    items = :items,
                    updated_at = NOW()
                WHERE id = :id
            ");
        } else {
            // INSERT
            $stmt = $this->pdo->prepare("
                INSERT INTO invoices (
                    invoice_number, reservation_id, user_id,
                    subtotal, tax_rate, tax_amount, total_amount,
                    currency, status,
                    issue_date, due_date,
                    items,
                    created_at, updated_at
                )
                VALUES (
                    :invoice_number, :reservation_id, :user_id,
                    :subtotal, :tax_rate, :tax_amount, :total_amount,
                    :currency, :status,
                    :issue_date, :due_date,
                    :items,
                    NOW(), NOW()
                )
            ");
        }

        $stmt->execute([
            ':id'            => $invoice->getId(),
            ':invoice_number'=> $invoice->getInvoiceNumber(),
            ':reservation_id'=> $invoice->getReservationId(),
            ':user_id'       => $invoice->getUserId(),

            ':subtotal'      => $invoice->getSubtotal(),
            ':tax_rate'      => $invoice->getTaxRate(),
            ':tax_amount'    => $invoice->getTaxAmount(),
            ':total_amount'  => $invoice->getTotalAmount(),

            ':currency'      => $invoice->getCurrency(),
            ':status'        => $invoice->getStatus(),

            ':issue_date'    => $invoice->getIssueDate()->format('Y-m-d H:i:s'),
            ':due_date'      => $invoice->getDueDate()->format('Y-m-d H:i:s'),

            ':items'         => $itemsJson
        ]);
    }

    public function findById(string $id): ?Invoice
    {
        $stmt = $this->pdo->prepare("SELECT * FROM invoices WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return $this->mapRowToInvoice($row);
    }

    public function findByReservationId(string $reservationId): ?Invoice
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM invoices WHERE reservation_id = :res LIMIT 1
        ");
        $stmt->execute([':res' => $reservationId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return $this->mapRowToInvoice($row);
    }

    private function mapRowToInvoice(array $row): Invoice
    {
        $invoice = new Invoice(
            $row['invoice_number'],
            (int)$row['reservation_id'],
            (int)$row['user_id']
        );

        // ID
        $invoice->setId((int)$row['id']);

        // VALUES
        $invoice->setSubtotal((float)$row['subtotal']);
        $invoice->setTaxRate((float)$row['tax_rate']);
        $invoice->setCurrency($row['currency']);
        $invoice->setStatus($row['status']);

    

        return $invoice;
    }
}
