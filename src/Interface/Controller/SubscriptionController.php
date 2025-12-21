<?php

namespace App\Interface\Controller;

use App\UseCase\Subscription\CreateSubscription;
use App\UseCase\Subscription\ListUserSubscriptions;
use App\UseCase\Subscription\CancelSubscription;

class SubscriptionController
{
    private CreateSubscription $createSubscription;
    private ListUserSubscriptions $listUserSubscriptions;
    private CancelSubscription $cancelSubscription;

    public function __construct(
        CreateSubscription $createSubscription,
        ListUserSubscriptions $listUserSubscriptions,
        CancelSubscription $cancelSubscription
    ) {
        $this->createSubscription = $createSubscription;
        $this->listUserSubscriptions = $listUserSubscriptions;
        $this->cancelSubscription = $cancelSubscription;
    }

    public function list(): void
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        try {
            $subscriptions = $this->listUserSubscriptions->execute($_SESSION['user_id']);
            
            require __DIR__ . '/../View/subscriptions.php';
        } catch (\Exception $e) {
            $error = $e->getMessage();
            require __DIR__ . '/../View/subscriptions.php';
        }
    }

    public function create(): void
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $parkingId = $_POST['parking_id'] ?? '';
                $monthsDuration = (int)($_POST['months_duration'] ?? 1);
                
                $weeklySchedule = [];
                for ($day = 0; $day <= 6; $day++) {
                    if (isset($_POST["day_{$day}_enabled"])) {
                        $startTime = $_POST["day_{$day}_start"] ?? '09:00';
                        $endTime = $_POST["day_{$day}_end"] ?? '18:00';
                        
                        $weeklySchedule[$day] = [
                            ['start' => $startTime, 'end' => $endTime]
                        ];
                    }
                }

                $subscription = $this->createSubscription->execute(
                    $_SESSION['user_id'],
                    $parkingId,
                    $weeklySchedule,
                    $monthsDuration
                );

                header('Location: /subscriptions');
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        require __DIR__ . '/../View/subscription_form.php';
    }

    public function cancel(): void
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $subscriptionId = $_POST['subscription_id'] ?? '';
                
                $this->cancelSubscription->execute($subscriptionId, $_SESSION['user_id']);
                
                header('Location: /subscriptions');
                exit;
            } catch (\Exception $e) {
                $error = $e->getMessage();
                header('Location: /subscriptions?error=' . urlencode($error));
                exit;
            }
        }

        header('Location: /subscriptions');
        exit;
    }
}