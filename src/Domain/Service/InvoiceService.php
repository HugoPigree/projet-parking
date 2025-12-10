<?php
namespace App\Domain\Service;

use App\Domain\Entity\Invoice;
use App\Domain\Entity\Reservation;
use App\Domain\Repository\InvoiceRepositoryInterface;
use App\Domain\Service\PricingService;
use Dompdf\Dompdf;
class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepo,
        private PricingService $pricingService
    ) {}

    public function generateInvoiceFromReservation(Reservation $reservation): Invoice
    {
        $existing = $this->invoiceRepo->findByReservationId($reservation->getId());
        if ($existing) {
            return $existing;
        }

        $priceItems = $this->pricingService->breakdownPriceItems($reservation->getStartTime(), $reservation->getEndTime(), $reservation);

        $invoice = new Invoice(uniqid('inv_'), $reservation->getId());

      if($priceItems !=null){
          foreach ($priceItems as $pi) {
            $invoice->addItem($pi['label'], (int)$pi['qty'], (float)$pi['unit']);
        }
      }


        $this->invoiceRepo->save($invoice);
        return $invoice;
    }

    /**
     * Generate HTML view of invoice (string)
     */
    public function renderInvoiceHtml(Invoice $invoice, array $context = []): string
    {
        // simple HTML; you can replace with template engine
        $html = '<html><head><meta charset="utf-8"><style>
            body{font-family: Arial, sans-serif; font-size: 12px;}
            .items{width:100%;border-collapse:collapse;}
            .items th, .items td{border:1px solid #ddd;padding:8px;}
        </style></head><body>';
        $html .= '<h2>Invoice '.$invoice->getId().'</h2>';
        $html .= '<p>Reservation: '.$invoice->getReservationId().'</p>';
        $html .= '<table class="items"><thead><tr><th>Label</th><th>QTY</th><th>Unit</th><th>Total</th></tr></thead><tbody>';
        foreach ($invoice->getItems() as $it) {
            $html .= '<tr><td>'.htmlspecialchars($it['label']).'</td><td>'.$it['qty'].'</td><td>'.number_format($it['unit'],2).'€</td><td>'.number_format($it['total'],2).'€</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<p><strong>Penalty:</strong> '.number_format($invoice->getPenalty(),2).'€</p>';
        $html .= '<h3>Total: '.number_format($invoice->getTotal(),2).'€</h3>';
        $html .= '</body></html>';
        return $html;
    }

    /**
     * Generate PDF binary using Dompdf
     */
    public function generatePdfBinary(Invoice $invoice): string
    {
        $html = $this->renderInvoiceHtml($invoice);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }
}
