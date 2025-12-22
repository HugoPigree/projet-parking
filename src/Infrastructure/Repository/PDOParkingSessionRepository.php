<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ParkingSession;
use App\Infrastructure\SQL\Database;
use PDO;
use DateTime;

class PDOParkingSessionRepository implements ParkingSessionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    public function save(ParkingSession $session): void
    {
        // Convertir l'ID string en int si nécessaire pour la DB
        $id = $session->getId() ? (is_numeric($session->getId()) ? (int)$session->getId() : null) : null;

        if ($id && $this->findById($session->getId())) {
            $this->update($session);
        } else {
            $this->insert($session);
        }
    }

    private function insert(ParkingSession $session): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO parking_sessions (user_id, parking_id, reservation_id, subscription_id, entry_time, exit_time, created_at)
            VALUES (:user_id, :parking_id, :reservation_id, :subscription_id, :entry_time, :exit_time, NOW())
        ");

        $stmt->execute([
            ':user_id' => (int)$session->getUserId(),
            ':parking_id' => (int)$session->getParkingId(),
            ':reservation_id' => $session->getReservationId() ? (int)$session->getReservationId() : null,
            ':subscription_id' => $session->getSubscriptionId() ? (int)$session->getSubscriptionId() : null,
            ':entry_time' => $session->getEntryTime()->format('Y-m-d H:i:s'),
            ':exit_time' => $session->getExitTime()?->format('Y-m-d H:i:s'),
        ]);

        // Note: l'ID sera auto-incrémenté par MySQL mais l'entité utilise string
        // On ne peut pas facilement récupérer et setter l'ID sans modifier l'entité
    }

    private function update(ParkingSession $session): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE parking_sessions
            SET exit_time = :exit_time
            WHERE id = :id
        ");

        $stmt->execute([
            ':exit_time' => $session->getExitTime()?->format('Y-m-d H:i:s'),
            ':id' => (int)$session->getId(),
        ]);
    }

    public function findById(string $id): ?ParkingSession
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parking_sessions WHERE id = :id LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrateSession($row);
    }

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parking_sessions
            WHERE user_id = :user_id
            ORDER BY entry_time DESC
        ");
        $stmt->execute([':user_id' => (int)$userId]);

        $sessions = [];
        while ($row = $stmt->fetch()) {
            $sessions[] = $this->hydrateSession($row);
        }

        return $sessions;
    }

    public function findByParkingId(string $parkingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parking_sessions
            WHERE parking_id = :parking_id
            ORDER BY entry_time DESC
        ");
        $stmt->execute([':parking_id' => (int)$parkingId]);

        $sessions = [];
        while ($row = $stmt->fetch()) {
            $sessions[] = $this->hydrateSession($row);
        }

        return $sessions;
    }

    public function findActiveByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parking_sessions
            WHERE user_id = :user_id AND exit_time IS NULL
            ORDER BY entry_time DESC
        ");
        $stmt->execute([':user_id' => (int)$userId]);

        $sessions = [];
        while ($row = $stmt->fetch()) {
            $sessions[] = $this->hydrateSession($row);
        }

        return $sessions;
    }

    public function findActiveByParkingId(string $parkingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parking_sessions
            WHERE parking_id = :parking_id AND exit_time IS NULL
            ORDER BY entry_time DESC
        ");
        $stmt->execute([':parking_id' => (int)$parkingId]);

        $sessions = [];
        while ($row = $stmt->fetch()) {
            $sessions[] = $this->hydrateSession($row);
        }

        return $sessions;
    }

    public function findByReservationId(string $reservationId): ?ParkingSession
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parking_sessions
            WHERE reservation_id = :reservation_id
            LIMIT 1
        ");
        $stmt->execute([':reservation_id' => (int)$reservationId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrateSession($row);
    }

    public function findBySubscriptionId(string $subscriptionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parking_sessions
            WHERE subscription_id = :subscription_id
            ORDER BY entry_time DESC
        ");
        $stmt->execute([':subscription_id' => (int)$subscriptionId]);

        $sessions = [];
        while ($row = $stmt->fetch()) {
            $sessions[] = $this->hydrateSession($row);
        }

        return $sessions;
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM parking_sessions WHERE id = :id");
        $stmt->execute(['id' => (int)$id]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM parking_sessions
            ORDER BY entry_time DESC
        ");

        $sessions = [];
        while ($row = $stmt->fetch()) {
            $sessions[] = $this->hydrateSession($row);
        }

        return $sessions;
    }

    private function hydrateSession(array $row): ParkingSession
    {
        $session = new ParkingSession(
            (string)$row['id'], // ID en string comme attendu par l'entité
            (string)$row['user_id'],
            (string)$row['parking_id'],
            new DateTime($row['entry_time']),
            $row['exit_time'] ? new DateTime($row['exit_time']) : null,
            $row['reservation_id'] ? (string)$row['reservation_id'] : null,
            $row['subscription_id'] ? (string)$row['subscription_id'] : null
        );

        return $session;
    }
}
