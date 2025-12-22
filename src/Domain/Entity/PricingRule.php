<?php

namespace App\Domain\Entity;

/**
 * Value Object représentant une règle tarifaire
 * 
 * Chaque parking peut avoir plusieurs règles tarifaires qui définissent
 * le prix par tranche de temps. Le tarif peut être dégressif.
 */
class PricingRule
{
    private int $intervalMinutes;
    private float $pricePerInterval;
    private ?int $maxDurationMinutes;

    /**
     * Constructeur de PricingRule
     * 
     * @param int $intervalMinutes Durée de la tranche en minutes (ex: 15 pour 15 minutes)
     * @param float $pricePerInterval Prix pour cette tranche
     * @param int|null $maxDurationMinutes Durée maximale en minutes pour laquelle cette règle s'applique (null = illimité)
     */
    public function __construct(
        int $intervalMinutes,
        float $pricePerInterval,
        ?int $maxDurationMinutes = null
    ) {
        if ($intervalMinutes <= 0) {
            throw new \InvalidArgumentException('Interval minutes must be greater than 0');
        }
        if ($pricePerInterval < 0) {
            throw new \InvalidArgumentException('Price per interval cannot be negative');
        }
        if ($maxDurationMinutes !== null && $maxDurationMinutes <= 0) {
            throw new \InvalidArgumentException('Max duration must be greater than 0');
        }

        $this->intervalMinutes = $intervalMinutes;
        $this->pricePerInterval = $pricePerInterval;
        $this->maxDurationMinutes = $maxDurationMinutes;
    }

    /**
     * Calcule le prix pour une durée donnée
     * 
     * @param int $durationMinutes Durée totale en minutes
     * @return float Prix calculé pour cette durée
     */
    public function getPriceForDuration(int $durationMinutes): float
    {
        if ($durationMinutes <= 0) {
            return 0.0;
        }

        // Si une durée maximale est définie et que la durée demandée dépasse cette limite,
        // on calcule seulement jusqu'à la limite
        $effectiveDuration = $this->maxDurationMinutes !== null 
            ? min($durationMinutes, $this->maxDurationMinutes)
            : $durationMinutes;

        // Calcul du nombre de tranches (arrondi au supérieur)
        $numberOfIntervals = ceil($effectiveDuration / $this->intervalMinutes);

        return $numberOfIntervals * $this->pricePerInterval;
    }

    /**
     * Vérifie si cette règle s'applique pour une durée donnée
     * 
     * @param int $durationMinutes Durée en minutes
     * @return bool True si la règle s'applique
     */
    public function appliesToDuration(int $durationMinutes): bool
    {
        if ($this->maxDurationMinutes === null) {
            return true;
        }

        return $durationMinutes <= $this->maxDurationMinutes;
    }

    // Getters
    public function getIntervalMinutes(): int
    {
        return $this->intervalMinutes;
    }

    public function getPricePerInterval(): float
    {
        return $this->pricePerInterval;
    }

    public function getMaxDurationMinutes(): ?int
    {
        return $this->maxDurationMinutes;
    }

    /**
     * Convertit la règle en tableau pour stockage
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'intervalMinutes' => $this->intervalMinutes,
            'pricePerInterval' => $this->pricePerInterval,
            'maxDurationMinutes' => $this->maxDurationMinutes
        ];
    }

    /**
     * Crée une PricingRule à partir d'un tableau
     * 
     * @param array $data Données de la règle
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['intervalMinutes'] ?? 15,
            $data['pricePerInterval'] ?? 0.0,
            $data['maxDurationMinutes'] ?? null
        );
    }
}

