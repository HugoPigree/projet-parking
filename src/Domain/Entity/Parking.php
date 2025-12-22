<?php

namespace App\Domain\Entity;

/**
 * Entité Parking représentant un parking partagé
 * 
 * Un parking contient :
 * - Des coordonnées GPS
 * - Un nombre de places de parkings
 * - Un tarif horaire qui peut varier avec le temps
 * - Des horaires d'ouverture
 * - Une liste de réservations (référencées par ID)
 * - Une liste de stationnements (référencés par ID)
 */
class Parking
{
    private ?int $id;
    private int $ownerId;
    private string $name;
    private string $address;
    private float $latitude;
    private float $longitude;
    private int $totalSpots;
    private array $openingHours;
    private array $pricingRules;
    private array $reservationIds;
    private array $stationnementIds;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    /**
     * Constructeur de l'entité Parking
     * 
     * @param int $ownerId ID du propriétaire du parking
     * @param string $name Nom du parking
     * @param string $address Adresse du parking
     * @param float $latitude Coordonnée GPS latitude
     * @param float $longitude Coordonnée GPS longitude
     * @param int $totalSpots Nombre total de places
     * @param array $openingHours Horaires d'ouverture
     * @param array $pricingRules Grille tarifaire
     * @param int|null $id ID du parking (null si nouveau)
     */
    public function __construct(
        int $ownerId,
        string $name,
        string $address,
        float $latitude,
        float $longitude,
        int $totalSpots,
        array $openingHours = [],
        array $pricingRules = [],
        ?int $id = null
    ) {
        $this->id = $id;
        $this->ownerId = $ownerId;
        $this->name = $name;
        $this->address = $address;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->totalSpots = $totalSpots;
        $this->openingHours = $openingHours;
        $this->pricingRules = $pricingRules;
        $this->reservationIds = [];
        $this->stationnementIds = [];
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getTotalSpots(): int
    {
        return $this->totalSpots;
    }

    public function getOpeningHours(): array
    {
        return $this->openingHours;
    }

    public function getPricingRules(): array
    {
        return $this->pricingRules;
    }

    public function getReservationIds(): array
    {
        return $this->reservationIds;
    }

    public function getStationnementIds(): array
    {
        return $this->stationnementIds;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setOwnerId(int $ownerId): void
    {
        $this->ownerId = $ownerId;
        $this->updatedAt = new \DateTime();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new \DateTime();
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
        $this->updatedAt = new \DateTime();
    }

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
        $this->updatedAt = new \DateTime();
    }

    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
        $this->updatedAt = new \DateTime();
    }

    public function setTotalSpots(int $totalSpots): void
    {
        $this->totalSpots = $totalSpots;
        $this->updatedAt = new \DateTime();
    }

    public function setOpeningHours(array $openingHours): void
    {
        $this->openingHours = $openingHours;
        $this->updatedAt = new \DateTime();
    }

    public function setPricingRules(array $pricingRules): void
    {
        $this->pricingRules = $pricingRules;
        $this->updatedAt = new \DateTime();
    }

    public function setReservationIds(array $reservationIds): void
    {
        $this->reservationIds = $reservationIds;
        $this->updatedAt = new \DateTime();
    }

    public function setStationnementIds(array $stationnementIds): void
    {
        $this->stationnementIds = $stationnementIds;
        $this->updatedAt = new \DateTime();
    }

    // Méthodes métier

    /**
     * Vérifie si le parking a au moins une place disponible
     * 
     * @param int $occupiedSpots Nombre de places occupées
     * @return bool True si au moins une place est disponible
     */
    public function hasAvailableSpot(int $occupiedSpots = 0): bool
    {
        return $occupiedSpots < $this->totalSpots;
    }

    /**
     * Vérifie si le parking est ouvert à un moment donné
     * 
     * @param \DateTime $timestamp Timestamp à vérifier
     * @return bool True si le parking est ouvert à ce moment
     */
    public function isOpenAt(\DateTime $timestamp): bool
    {
        // Si pas d'horaires définis, le parking est ouvert en permanence
        if (empty($this->openingHours)) {
            return true;
        }

        $dayOfWeek = (int)$timestamp->format('N'); // 1 (lundi) à 7 (dimanche)
        $time = $timestamp->format('H:i:s');

        foreach ($this->openingHours as $schedule) {
            // Format attendu : ['day' => 1-7, 'start' => 'HH:MM', 'end' => 'HH:MM']
            // ou ['start' => 'HH:MM', 'end' => 'HH:MM'] pour tous les jours
            
            if (isset($schedule['day']) && $schedule['day'] !== $dayOfWeek) {
                continue;
            }

            $startTime = $schedule['start'] ?? '00:00';
            $endTime = $schedule['end'] ?? '23:59';

            // Gestion des créneaux qui passent minuit (ex: 18h - 8h)
            if ($startTime > $endTime) {
                // Le créneau passe minuit
                if ($time >= $startTime || $time <= $endTime) {
                    return true;
                }
            } else {
                // Créneau normal
                if ($time >= $startTime && $time <= $endTime) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Calcule le nombre de places disponibles
     * 
     * @param int $occupiedSpots Nombre de places occupées
     * @return int Nombre de places disponibles
     */
    public function getAvailableSpots(int $occupiedSpots = 0): int
    {
        $available = $this->totalSpots - $occupiedSpots;
        return max(0, $available);
    }

    /**
     * Ajoute une réservation à la liste
     * 
     * @param int $reservationId ID de la réservation
     * @return void
     */
    public function addReservationId(int $reservationId): void
    {
        if (!in_array($reservationId, $this->reservationIds, true)) {
            $this->reservationIds[] = $reservationId;
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * Retire une réservation de la liste
     * 
     * @param int $reservationId ID de la réservation
     * @return void
     */
    public function removeReservationId(int $reservationId): void
    {
        $key = array_search($reservationId, $this->reservationIds, true);
        if ($key !== false) {
            unset($this->reservationIds[$key]);
            $this->reservationIds = array_values($this->reservationIds);
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * Ajoute un stationnement à la liste
     * 
     * @param int $stationnementId ID du stationnement
     * @return void
     */
    public function addStationnementId(int $stationnementId): void
    {
        if (!in_array($stationnementId, $this->stationnementIds, true)) {
            $this->stationnementIds[] = $stationnementId;
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * Retire un stationnement de la liste
     * 
     * @param int $stationnementId ID du stationnement
     * @return void
     */
    public function removeStationnementId(int $stationnementId): void
    {
        $key = array_search($stationnementId, $this->stationnementIds, true);
        if ($key !== false) {
            unset($this->stationnementIds[$key]);
            $this->stationnementIds = array_values($this->stationnementIds);
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * Calcule la distance en kilomètres entre ce parking et des coordonnées GPS
     * Utilise la formule de Haversine
     * 
     * @param float $latitude Latitude de destination
     * @param float $longitude Longitude de destination
     * @return float Distance en kilomètres
     */
    public function calculateDistance(float $latitude, float $longitude): float
    {
        $earthRadius = 6371; // Rayon de la Terre en kilomètres

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

