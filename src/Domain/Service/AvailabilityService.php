<?php

namespace App\Domain\Service;

use App\Domain\Entity\Parking;

/**
 * Service de gestion de la disponibilité des parkings
 * 
 * Ce service calcule la disponibilité en tenant compte :
 * - Des réservations actives
 * - Des stationnements en cours
 * - Des abonnements actifs
 */
class AvailabilityService
{
    /**
     * Vérifie si un parking est disponible pour un créneau donné
     * 
     * @param Parking $parking Le parking à vérifier
     * @param \DateTime $startTime Début du créneau
     * @param \DateTime $endTime Fin du créneau
     * @param int $occupiedSpots Nombre de places occupées
     * @return bool True si le parking est disponible
     */
    public function isAvailable(
        Parking $parking,
        \DateTime $startTime,
        \DateTime $endTime,
        int $occupiedSpots = 0
    ): bool {
        // Vérifier si le parking est ouvert pendant le créneau
        if (!$parking->isOpenAt($startTime) || !$parking->isOpenAt($endTime)) {
            return false;
        }

        // Vérifier s'il y a des places disponibles
        return $parking->hasAvailableSpot($occupiedSpots);
    }

    /**
     * Calcule le nombre de places disponibles à un moment donné
     * 
     * @param Parking $parking Le parking
     * @param \DateTime $timestamp Moment à vérifier
     * @param int $occupiedSpots Nombre de places occupées
     * @return int Nombre de places disponibles
     */
    public function getAvailableSpots(
        Parking $parking,
        \DateTime $timestamp,
        int $occupiedSpots = 0
    ): int {
        // Si le parking n'est pas ouvert, aucune place disponible
        if (!$parking->isOpenAt($timestamp)) {
            return 0;
        }

        return $parking->getAvailableSpots($occupiedSpots);
    }

    /**
     * Vérifie si une réservation peut être effectuée
     * 
     * @param Parking $parking Le parking
     * @param \DateTime $startTime Début de la réservation
     * @param \DateTime $endTime Fin de la réservation
     * @param int $occupiedSpots Nombre de places occupées
     * @return bool True si la réservation est possible
     */
    public function canReserve(
        Parking $parking,
        \DateTime $startTime,
        \DateTime $endTime,
        int $occupiedSpots = 0
    ): bool {
        // Vérifier que les dates sont valides
        if ($startTime >= $endTime) {
            return false;
        }

        // Vérifier la disponibilité
        return $this->isAvailable($parking, $startTime, $endTime, $occupiedSpots);
    }

    /**
     * Vérifie si un parking a au moins une place disponible
     * 
     * @param Parking $parking Le parking
     * @param int $occupiedSpots Nombre de places occupées
     * @return bool True si au moins une place est disponible
     */
    public function hasAvailableSpot(Parking $parking, int $occupiedSpots = 0): bool
    {
        return $parking->hasAvailableSpot($occupiedSpots);
    }
}

