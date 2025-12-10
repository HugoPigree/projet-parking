<?php
namespace App\UseCase\Reservation;

use App\Domain\Entity\Reservation;
use App\Domain\Entity\Invoice;
use App\Infrastructure\Repository\PDOReservationRepository;
use App\Infrastructure\Repository\PDOInvoiceRepository;
use DateTime;
use Exception;

class CreateReservation
{
    private PDOReservationRepository $reservationRepository;
    private PDOInvoiceRepository $invoiceRepository;

    public function __construct(
        PDOReservationRepository $reservationRepository,
        PDOInvoiceRepository $invoiceRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    
    public function execute(
        int $userId,
        int $parkingId,
        int $slotId,
        DateTime $startTime,
        DateTime $endTime,
        float $pricePerHour
    ): array {
        // Génération d'un UUID pour la réservation
        $uuid = bin2hex(random_bytes(16));

        // Création de la réservation
        $reservation = new Reservation(
            $uuid,
            $userId,
            $parkingId,
            $slotId,
            $startTime,
            $endTime
        );

        // Sauvegarde de la réservation
        $this->reservationRepository->save($reservation);

        // Calcul du total
        $hours = max(1, ceil(($endTime->getTimestamp() - $startTime->getTimestamp()) / 3600));
        $subtotal = $hours * $pricePerHour;
        $taxRate = 18.0; 
        $invoiceNumber = 'INV-' . strtoupper(bin2hex(random_bytes(4)));

        // Création de la facture liée à la réservation
        $invoice = new Invoice(
            $invoiceNumber,
            $reservation->getId(),
            $userId
        );
        $invoice->setSubtotal($subtotal);
        $invoice->setTaxRate($taxRate);

        // Sauvegarde de la facture
        $this->invoiceRepository->save($invoice);

        return [
            'reservation' => $reservation,
            'invoice' => $invoice
        ];
    }
}
