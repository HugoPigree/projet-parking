<?php

namespace App\Interface\Controller;

use App\UseCase\Parking\CreateParking;
use App\UseCase\Parking\UpdateParking;
use App\UseCase\Parking\SearchAvailableParkings;
use App\UseCase\Parking\GetParkingAvailability;
use App\UseCase\Parking\GetParkingRevenue;
use App\Domain\Repository\ParkingRepositoryInterface;

/**
 * Controller pour la gestion des parkings
 */
class ParkingController
{
    private CreateParking $createParking;
    private UpdateParking $updateParking;
    private SearchAvailableParkings $searchAvailableParkings;
    private GetParkingAvailability $getParkingAvailability;
    private GetParkingRevenue $getParkingRevenue;
    private ParkingRepositoryInterface $parkingRepository;

    public function __construct(
        CreateParking $createParking,
        UpdateParking $updateParking,
        SearchAvailableParkings $searchAvailableParkings,
        GetParkingAvailability $getParkingAvailability,
        GetParkingRevenue $getParkingRevenue,
        ParkingRepositoryInterface $parkingRepository
    ) {
        $this->createParking = $createParking;
        $this->updateParking = $updateParking;
        $this->searchAvailableParkings = $searchAvailableParkings;
        $this->getParkingAvailability = $getParkingAvailability;
        $this->getParkingRevenue = $getParkingRevenue;
        $this->parkingRepository = $parkingRepository;
    }

    /**
     * Recherche des parkings disponibles
     * 
     * @param array $params Paramètres de la requête
     * @return array|string Réponse JSON ou HTML
     */
    public function search(array $params)
    {
        $latitude = (float)($params['latitude'] ?? 0);
        $longitude = (float)($params['longitude'] ?? 0);
        $radiusKm = (float)($params['radius'] ?? 5.0);
        $startTime = isset($params['startTime']) ? new \DateTime($params['startTime']) : null;
        $endTime = isset($params['endTime']) ? new \DateTime($params['endTime']) : null;

        try {
            $parkings = $this->searchAvailableParkings->execute($latitude, $longitude, $radiusKm, $startTime, $endTime);

            if ($this->isApiRequest($params)) {
                return $this->jsonResponse([
                    'success' => true,
                    'parkings' => $this->parkingsToArray($parkings)
                ]);
            }

            // Vue HTML
            return $this->renderView('parking_search', [
                'parkings' => $parkings,
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Crée un nouveau parking
     * 
     * @param array $params Paramètres de la requête
     * @return array|string Réponse JSON ou HTML
     */
    public function create(array $params)
    {
        try {
            $parking = $this->createParking->execute(
                ownerId: (int)$params['ownerId'],
                name: $params['name'] ?? '',
                address: $params['address'] ?? '',
                latitude: (float)($params['latitude'] ?? 0),
                longitude: (float)($params['longitude'] ?? 0),
                totalSpots: (int)($params['totalSpots'] ?? 0),
                openingHours: json_decode($params['openingHours'] ?? '[]', true),
                pricingRules: json_decode($params['pricingRules'] ?? '[]', true)
            );

            if ($this->isApiRequest($params)) {
                return $this->jsonResponse([
                    'success' => true,
                    'parking' => $this->parkingToArray($parking)
                ]);
            }

            return $this->renderView('parking_form', [
                'success' => true,
                'message' => 'Parking créé avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Met à jour un parking
     * 
     * @param array $params Paramètres de la requête
     * @return array|string Réponse JSON ou HTML
     */
    public function update(array $params)
    {
        try {
            $parking = $this->updateParking->execute(
                parkingId: (int)$params['id'],
                ownerId: (int)$params['ownerId'],
                data: $params
            );

            if ($this->isApiRequest($params)) {
                return $this->jsonResponse([
                    'success' => true,
                    'parking' => $this->parkingToArray($parking)
                ]);
            }

            return $this->renderView('parking_form', [
                'success' => true,
                'message' => 'Parking mis à jour avec succès',
                'parking' => $parking
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Affiche les détails d'un parking
     * 
     * @param array $params Paramètres de la requête
     * @return array|string Réponse JSON ou HTML
     */
    public function show(array $params)
    {
        $parking = $this->parkingRepository->findById((int)$params['id']);

        if ($parking === null) {
            return $this->errorResponse('Parking non trouvé');
        }

        if ($this->isApiRequest($params)) {
            return $this->jsonResponse([
                'success' => true,
                'parking' => $this->parkingToArray($parking)
            ]);
        }

        return $this->renderView('parking_details', ['parking' => $parking]);
    }

    /**
     * Obtient la disponibilité d'un parking
     * 
     * @param array $params Paramètres de la requête
     * @return array|string Réponse JSON ou HTML
     */
    public function availability(array $params)
    {
        try {
            $timestamp = isset($params['timestamp']) 
                ? new \DateTime($params['timestamp']) 
                : new \DateTime();

            $availability = $this->getParkingAvailability->execute(
                parkingId: (int)$params['id'],
                timestamp: $timestamp
            );

            if ($this->isApiRequest($params)) {
                return $this->jsonResponse([
                    'success' => true,
                    'availability' => $availability
                ]);
            }

            return $this->renderView('parking_availability', $availability);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Obtient le chiffre d'affaire d'un parking
     * 
     * @param array $params Paramètres de la requête
     * @return array|string Réponse JSON ou HTML
     */
    public function revenue(array $params)
    {
        try {
            $year = (int)($params['year'] ?? date('Y'));
            $month = (int)($params['month'] ?? date('m'));

            $revenue = $this->getParkingRevenue->execute(
                parkingId: (int)$params['id'],
                ownerId: (int)$params['ownerId'],
                year: $year,
                month: $month
            );

            if ($this->isApiRequest($params)) {
                return $this->jsonResponse([
                    'success' => true,
                    'revenue' => $revenue
                ]);
            }

            return $this->renderView('parking_revenue', $revenue);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // Méthodes utilitaires

    private function isApiRequest(array $params): bool
    {
        return isset($params['format']) && $params['format'] === 'json';
    }

    private function jsonResponse(array $data): array
    {
        return $data;
    }

    private function renderView(string $viewName, array $data = []): string
    {
        // TODO: Implémenter le système de rendu de vues
        ob_start();
        extract($data);
        include __DIR__ . "/../View/{$viewName}.php";
        return ob_get_clean();
    }

    private function errorResponse(string $message): array|string
    {
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            return ['success' => false, 'error' => $message];
        }
        return "Erreur : $message";
    }

    private function parkingToArray($parking): array
    {
        return [
            'id' => $parking->getId(),
            'ownerId' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'totalSpots' => $parking->getTotalSpots(),
            'openingHours' => $parking->getOpeningHours(),
            'pricingRules' => $parking->getPricingRules()
        ];
    }

    private function parkingsToArray(array $parkings): array
    {
        return array_map([$this, 'parkingToArray'], $parkings);
    }
}

