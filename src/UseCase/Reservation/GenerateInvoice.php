<?php
namespace App\UseCase\Invoice;

use App\Domain\Entity\Invoice;
use App\Infrastructure\Repository\PDOInvoiceRepository;
use Dompdf\Dompdf;

class GenerateInvoice
{
    private PDOInvoiceRepository $invoiceRepository;

    public function __construct(PDOInvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Génère un PDF pour une facture.
     *
     * @param string $invoiceId
     * @return string PDF binaire
     */
    public function execute(string $invoiceId): string
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);

        if (!$invoice) {
            throw new \Exception("Invoice not found: $invoiceId");
        }

        $html = $this->renderHtml($invoice);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function renderHtml(Invoice $invoice): string
    {
        
        return "
        <h1>Invoice: {$invoice->getInvoiceNumber()}</h1>
        <p>Reservation ID: {$invoice->getReservationId()}</p>
        <p>User ID: {$invoice->getUserId()}</p>
        <p>Issue Date: {$invoice->getIssueDate()->format('Y-m-d')}</p>
        <table border='1' cellpadding='5' cellspacing='0'>
            <thead>
                <tr><th>Label</th><th>Qty</th><th>Unit</th><th>Total</th></tr>
            </thead>
           
              
            >
        </table>
        <p>Subtotal: {$invoice->getSubtotal()}</p>
        <p>Tax ({$invoice->getTaxRate()}%): {$invoice->getTaxAmount()}</p>
        <p>Total: {$invoice->getTotalAmount()} {$invoice->getCurrency()}</p>
        ";
    }
}
