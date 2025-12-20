<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Parking;

/**
 * Interface du repository pour les parkings
 * 
 * Cette interface définit les méthodes nécessaires pour gérer
 * les parkings dans le système, indépendamment du stockage utilisé
 */
interface ParkingRepositoryInterface
{
    /**
     * Trouve un parking par son ID
     * 
     * @param int $id ID du parking
     * @return Parking|null Le parking ou null si non trouvé
     */
    public function findById(int $id): ?Parking;

    /**
     * Trouve tous les parkings
     * 
     * @return array Liste des parkings
     */
    public function findAll(): array;

    /**
     * Trouve tous les parkings d'un propriétaire
     * 
     * @param int $ownerId ID du propriétaire
     * @return array Liste des parkings du propriétaire
     */
    public function findByOwner(int $ownerId): array;

    /**
     * Trouve les parkings proches d'une coordonnée GPS
     * 
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param float $radiusKm Rayon de recherche en kilomètres
     * @return array Liste des parkings trouvés
     */
    public function findNearby(float $latitude, float $longitude, float $radiusKm = 5.0): array;

    /**
     * Sauvegarde un parking (création ou mise à jour)
     * 
     * @param Parking $parking Le parking à sauvegarder
     * @return Parking Le parking sauvegardé avec son ID
     */
    public function save(Parking $parking): Parking;

    /**
     * Supprime un parking
     * 
     * @param int $id ID du parking à supprimer
     * @return bool True si la suppression a réussi
     */
    public function delete(int $id): bool;

    /**
     * Vérifie si un parking existe
     * 
     * @param int $id ID du parking
     * @return bool True si le parking existe
     */
    public function exists(int $id): bool;
}

