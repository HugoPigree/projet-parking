<?php
namespace App\Interface\Controller;

use App\UseCase\Reservation\ListUserReservations;
use App\UseCase\Parking\CreateReservation;
use App\UseCase\Reservation\CancelReservation;
use App\UseCase\Reservation\GetReservation;
use App\UseCase\Reservation\DeleteReservation;

/**
 * Contrôleur pour la gestion des réservations.
 */
class ReservationController {

    public function __construct(
        private ListUserReservations $listUserReservations,
        private CreateReservation $createReservation,
        private CancelReservation $cancelReservation,
        private GetReservation $getReservation,
        private DeleteReservation $deleteReservation
    ) {}

    /**
     * Liste toutes les réservations d'un utilisateur
     */
    public function listForUser(string $userId): void {
        $reservations = $this->listUserReservations->execute($userId);
        $data = [ 'reservations' => $reservations ];
        include __DIR__ . '/../View/reservations.php';
    }


    /**
     * Affiche une seule réservation
     */
    public function show(string $reservationId): void {
        $reservation = $this->getReservation->execute($reservationId);

        if (!$reservation) {
            http_response_code(404);
            echo "Reservation not found";
            return;
        }

        $data = [ 'reservation' => $reservation ];
        include __DIR__ . '/../View/reservation_show.php';
    }


    /**
     * Affiche le formulaire de création
     */
    public function createForm(): void {
        include __DIR__ . '/../View/reservation_form.php';
    }


    /**
     * Soumission du formulaire de création
     */
    public function createSubmit(): void {

        $parkingId = $_POST['parkingId'] ?? null;
        $userId    = $_POST['userId'] ?? null;
        $start     = $_POST['start'] ?? null;
        $end       = $_POST['end'] ?? null;

        if (!$parkingId || !$userId || !$start || !$end) {
            echo "Missing fields";
            return;
        }

        // try {
        //     $reservation = $this->createReservation->execute(
        //         $parkingId,
        //         $userId,
        //         new \DateTime($start),
        //         new \DateTime($end)
        //     );

        //     header("Location: index.php?route=reservation.show&id=" . $reservation->getId());
        // } catch (\Exception $e) {
        //     echo "Erreur: " . $e->getMessage();
        // }
    }


    /**
     * Annuler une réservation
     */
    public function cancel(string $reservationId, string $userId): void
    {
        try {
            $this->cancelReservation->execute($reservationId, $userId);
            header("Location: index.php?route=reservations");
        } catch (\Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
    }


    /**
     * Supprimer une réservation (admin ou user owner)
     */
    public function delete(string $reservationId): void
    {
        try {
            $this->deleteReservation->execute($reservationId);
            header("Location: index.php?route=reservations");
        } catch (\Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
    }
}
