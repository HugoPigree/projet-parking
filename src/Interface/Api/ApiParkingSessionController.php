<?php

namespace App\Interface\Api;

use App\UseCase\ParkingSession\EnterParking;
use App\UseCase\ParkingSession\ExitParking;

class ApiParkingSessionController
{
    private EnterParking $enterParking;
    private ExitParking $exitParking;

    public function __construct(
        EnterParking $enterParking,
        ExitParking $exitParking
    ) {
        $this->enterParking = $enterParking;
        $this->exitParking = $exitParking;
    }

    public function enter(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['parking_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing parking_id']);
            return;
        }

        try {
            $session = $this->enterParking->execute(
                $_SESSION['user_id'],
                $input['parking_id'],
                $input['reservation_id'] ?? null
            );

            echo json_encode([
                'success' => true,
                'session' => [
                    'id' => $session->getId(),
                    'parking_id' => $session->getParkingId(),
                    'entry_time' => $session->getEntryTime()->format('Y-m-d H:i:s'),
                    'reservation_id' => $session->getReservationId(),
                    'subscription_id' => $session->getSubscriptionId()
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function exit(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            if (isset($input['session_id'])) {
                $this->exitParking->executeBySessionId($input['session_id'], $_SESSION['user_id']);
            } else {
                $this->exitParking->execute($_SESSION['user_id']);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Successfully exited parking'
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}