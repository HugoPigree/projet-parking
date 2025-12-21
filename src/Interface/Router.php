<?php

namespace App\Interface;

class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = require __DIR__ . '/routes.php';
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        
        if ($uri === '') {
            $uri = '/';
        }

        if (!isset($this->routes[$method])) {
            $this->handleNotFound();
            return;
        }

        if (!isset($this->routes[$method][$uri])) {
            $this->handleNotFound();
            return;
        }

        $handler = $this->routes[$method][$uri];
        
        if (!is_array($handler) || count($handler) !== 2) {
            $this->handleError('Invalid route handler configuration');
            return;
        }

        [$controllerClass, $methodName] = $handler;

        if (!class_exists($controllerClass)) {
            $this->handleError("Controller class {$controllerClass} not found");
            return;
        }

        try {
            $controller = $this->instantiateController($controllerClass);
            
            if (!method_exists($controller, $methodName)) {
                $this->handleError("Method {$methodName} not found in {$controllerClass}");
                return;
            }

            $controller->{$methodName}();
        } catch (\Exception $e) {
            $this->handleError('Application error: ' . $e->getMessage());
        }
    }

    private function instantiateController(string $controllerClass): object
    {
        switch ($controllerClass) {
            case \App\Interface\Controller\SubscriptionController::class:
                return new \App\Interface\Controller\SubscriptionController(
                    new \App\UseCase\Subscription\CreateSubscription(
                        new \App\Infrastructure\InMemory\InMemorySubscriptionRepository(),
                        new \App\Infrastructure\InMemory\InMemoryUserRepository(),
                        new \App\Infrastructure\InMemory\InMemoryParkingRepository()
                    ),
                    new \App\UseCase\Subscription\ListUserSubscriptions(
                        new \App\Infrastructure\InMemory\InMemorySubscriptionRepository(),
                        new \App\Infrastructure\InMemory\InMemoryUserRepository()
                    ),
                    new \App\UseCase\Subscription\CancelSubscription(
                        new \App\Infrastructure\InMemory\InMemorySubscriptionRepository(),
                        new \App\Infrastructure\InMemory\InMemoryUserRepository()
                    )
                );

            case \App\Interface\Api\ApiParkingSessionController::class:
                return new \App\Interface\Api\ApiParkingSessionController(
                    new \App\UseCase\ParkingSession\EnterParking(
                        new \App\Infrastructure\InMemory\InMemoryParkingSessionRepository(),
                        new \App\Infrastructure\InMemory\InMemoryUserRepository(),
                        new \App\Infrastructure\InMemory\InMemoryParkingRepository(),
                        new \App\Infrastructure\InMemory\InMemoryReservationRepository(),
                        new \App\Infrastructure\InMemory\InMemorySubscriptionRepository(),
                        new \App\Domain\Service\AvailabilityService()
                    ),
                    new \App\UseCase\ParkingSession\ExitParking(
                        new \App\Infrastructure\InMemory\InMemoryParkingSessionRepository(),
                        new \App\Infrastructure\InMemory\InMemoryUserRepository()
                    )
                );

            default:
                throw new \Exception("Unknown controller: {$controllerClass}");
        }
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        echo "404 - Page not found";
    }

    private function handleError(string $message): void
    {
        http_response_code(500);
        echo "500 - Internal Server Error: {$message}";
    }
}