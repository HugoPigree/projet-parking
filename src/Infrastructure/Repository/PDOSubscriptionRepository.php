<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Subscription;
use App\Infrastructure\SQL\Database;
use PDO;
use DateTime;

class PDOSubscriptionRepository implements SubscriptionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    public function save(Subscription $subscription): void
    {
        $id = $subscription->getId() ? (is_numeric($subscription->getId()) ? (int)$subscription->getId() : null) : null;

        if ($id && $this->findById($subscription->getId())) {
            $this->update($subscription);
        } else {
            $this->insert($subscription);
        }
    }

    private function insert(Subscription $subscription): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO subscriptions (user_id, parking_id, start_date, end_date, weekly_schedule, months_duration, price, status, created_at, updated_at)
            VALUES (:user_id, :parking_id, :start_date, :end_date, :weekly_schedule, :months_duration, :price, :status, NOW(), NOW())
        ");

        $stmt->execute([
            'user_id' => (int)$subscription->getUserId(),
            'parking_id' => (int)$subscription->getParkingId(),
            'start_date' => $subscription->getStartDate()->format('Y-m-d'),
            'end_date' => $subscription->getEndDate()->format('Y-m-d'),
            'weekly_schedule' => json_encode($subscription->getWeeklySchedule()),
            'months_duration' => $subscription->getMonthsDuration(),
            'price' => 0.0, // TODO: calculer le prix
            'status' => 'ACTIVE', // Par défaut
        ]);
    }

    private function update(Subscription $subscription): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE subscriptions
            SET weekly_schedule = :weekly_schedule,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'weekly_schedule' => json_encode($subscription->getWeeklySchedule()),
            'id' => (int)$subscription->getId(),
        ]);
    }

    public function findById(string $id): ?Subscription
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions WHERE id = :id LIMIT 1
        ");
        $stmt->execute(['id' => (int)$id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrateSubscription($row);
    }

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['user_id' => (int)$userId]);

        $subscriptions = [];
        while ($row = $stmt->fetch()) {
            $subscriptions[] = $this->hydrateSubscription($row);
        }

        return $subscriptions;
    }

    public function findByParkingId(string $parkingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions
            WHERE parking_id = :parking_id
            ORDER BY created_at DESC
        ");
        $stmt->execute(['parking_id' => (int)$parkingId]);

        $subscriptions = [];
        while ($row = $stmt->fetch()) {
            $subscriptions[] = $this->hydrateSubscription($row);
        }

        return $subscriptions;
    }

    public function findActiveByUserIdAndParkingId(string $userId, string $parkingId): ?Subscription
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions
            WHERE user_id = :user_id
              AND parking_id = :parking_id
              AND status = 'ACTIVE'
              AND start_date <= CURDATE()
              AND end_date >= CURDATE()
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([
            'user_id' => (int)$userId,
            'parking_id' => (int)$parkingId,
        ]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->hydrateSubscription($row);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM subscriptions WHERE id = :id");
        $stmt->execute(['id' => (int)$id]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM subscriptions
            ORDER BY created_at DESC
        ");

        $subscriptions = [];
        while ($row = $stmt->fetch()) {
            $subscriptions[] = $this->hydrateSubscription($row);
        }

        return $subscriptions;
    }

    private function hydrateSubscription(array $row): Subscription
    {
        $weeklySchedule = json_decode($row['weekly_schedule'], true) ?? [];

        $subscription = new Subscription(
            (string)$row['id'],
            (string)$row['user_id'],
            (string)$row['parking_id'],
            new DateTime($row['start_date']),
            new DateTime($row['end_date']),
            $weeklySchedule,
            (int)$row['months_duration']
        );

        return $subscription;
    }
}
