<?php
namespace App\Interface\Controller;

use App\UseCase\Reservation\GenerateInvoice;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Service\InvoiceService;

class InvoiceController
{
    public function __construct(
        private GenerateInvoice $generateInvoice,
        private InvoiceRepositoryInterface $invoiceRepo,
        private InvoiceService $invoiceService,
    private \App\Domain\Service\PricingService $pricingService
    ) {}

    // GET /reservations/{id}/invoice -> return HTML
      public function invoiceHtml(string $reservationId)
    {
        $invoice = $this->generateInvoice->execute($reservationId);

        header('Content-Type: text/html; charset=utf-8');
        echo $this->invoiceService->renderInvoiceHtml($invoice);
    }

    // GET /invoice/{id}/pdf -> return application/pdf
    public function invoicePdf(string $invoiceId)
{
    $invoice = $this->invoiceRepo->findById($invoiceId);
    if (!$invoice) {
        http_response_code(404);
        echo "Invoice not found";
        return;
    }

    $svc = new \App\Domain\Service\InvoiceService(
        $this->invoiceRepo,
        $this->pricingService
    );

    $pdf = $svc->generatePdfBinary($invoice);

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="invoice-'.$invoice->getId().'.pdf"');
    echo $pdf;
}

}
