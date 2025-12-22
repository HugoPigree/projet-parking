<?php
namespace App\Domain\Repository;

use App\Domain\Entity\User;

/**
 * Contrat de stockage des utilisateurs.
 * Permet d'isoler la logique métier de tout stockage concret.
 */
interface UserRepositoryInterface {
    public function findByEmail(string $email): ?User;
    public function findById(string $id): ?User;
    public function save(User $user): void;
}
