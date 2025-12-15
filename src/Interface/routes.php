<?php

use App\Interface\Controller\SubscriptionController;
use App\Interface\Api\ApiParkingSessionController;

return [
    'GET' => [
        '/subscriptions' => [SubscriptionController::class, 'list'],
        '/subscriptions/create' => [SubscriptionController::class, 'create'],
    ],
    'POST' => [
        '/subscriptions' => [SubscriptionController::class, 'create'],
        '/subscriptions/cancel' => [SubscriptionController::class, 'cancel'],
        '/api/parking-session/enter' => [ApiParkingSessionController::class, 'enter'],
        '/api/parking-session/exit' => [ApiParkingSessionController::class, 'exit'],
    ]
];