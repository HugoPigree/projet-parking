<?php
namespace App\Infrastructure\Repository;

use App\Domain\Repository\ReservationRepositoryInterface;
use App\Domain\Entity\Reservation;
use App\Infrastructure\SQL\Database;
use PDO;
use DateTime;

class PDOReservationRepository implements ReservationRepositoryInterface
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance();
    }

    public function save(Reservation $reservation): void
    {
        $existing = $reservation->getId() ? $this->findById($reservation->getId()) : null;

        if ($existing) {
            $sql = "
                UPDATE reservations SET
                    uuid = :uuid,
                    user_id = :user_id,
                    parking_id = :parking_id,
                    slot_id = :slot_id,
                    start_time = :start_time,
                    end_time = :end_time,
                    actual_start_time = :actual_start_time,
                    actual_end_time = :actual_end_time,
                    status = :status,
                    price = :price,
                    penalty = :penalty,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $params = [
                ':id'               => $reservation->getId(),
                ':uuid'             => $reservation->getUuid(),
                ':user_id'          => $reservation->getUserId(),
                ':parking_id'       => $reservation->getParkingId(),
                ':slot_id'          => $reservation->getSlotId(),
                ':start_time'       => $reservation->getStartTime()->format('Y-m-d H:i:s'),
                ':end_time'         => $reservation->getEndTime()->format('Y-m-d H:i:s'),
                ':actual_start_time'=> $reservation->getActualStartTime()?->format('Y-m-d H:i:s'),
                ':actual_end_time'  => $reservation->getActualEndTime()?->format('Y-m-d H:i:s'),
                ':status'           => $reservation->getStatus(),
                ':price'            => $reservation->getTotalPrice(),
                ':penalty'          => $reservation->getPenalty(),
            ];
        } else {
            $sql = "
                INSERT INTO reservations (
                    uuid, user_id, parking_id, slot_id,
                    start_time, end_time,
                    actual_start_time, actual_end_time,
                    status, price, penalty, created_at, updated_at
                ) VALUES (
                    :uuid, :user_id, :parking_id, :slot_id,
                    :start_time, :end_time,
                    :actual_start_time, :actual_end_time,
                    :status, :price, :penalty, NOW(), NOW()
                )
            ";

            $params = [
                ':uuid'             => $reservation->getUuid(),
                ':user_id'          => $reservation->getUserId(),
                ':parking_id'       => $reservation->getParkingId(),
                ':slot_id'          => $reservation->getSlotId(),
                ':start_time'       => $reservation->getStartTime()->format('Y-m-d H:i:s'),
                ':end_time'         => $reservation->getEndTime()->format('Y-m-d H:i:s'),
                ':actual_start_time'=> $reservation->getActualStartTime()?->format('Y-m-d H:i:s'),
                ':actual_end_time'  => $reservation->getActualEndTime()?->format('Y-m-d H:i:s'),
                ':status'           => $reservation->getStatus(),
                ':price'            => $reservation->getTotalPrice(),
                ':penalty'          => $reservation->getPenalty(),
            ];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function findById(string $id): ?Reservation
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return $this->mapRowToReservation($row);
    }

    public function findByUser(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations
            WHERE user_id = :user_id
            ORDER BY start_time DESC
        ");
        $stmt->execute([':user_id' => $userId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => $this->mapRowToReservation($r), $rows);
    }

    public function findByParking(int $parkingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations
            WHERE parking_id = :parking_id
            ORDER BY start_time DESC
        ");
        $stmt->execute([':parking_id' => $parkingId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => $this->mapRowToReservation($r), $rows);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservations WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function mapRowToReservation(array $row): Reservation
    {
        $reservation = new Reservation(
            $row['uuid'],
            (int)$row['user_id'],
            (int)$row['parking_id'],
            (int)$row['slot_id'],
            new DateTime($row['start_time']),
            new DateTime($row['end_time'])
        );

        $reservation->setId((int)$row['id']);

        if (!empty($row['actual_start_time'])) {
            $reservation->setActualStart(new DateTime($row['actual_start_time']));
        }
        if (!empty($row['actual_end_time'])) {
            $reservation->setActualEnd(new DateTime($row['actual_end_time']));
        }

        // Hydrater price et penalty
        if (isset($row['price'])) {
            $reservation->setTotalPrice((float)$row['price']);
        }
        if (isset($row['penalty'])) {
            $reservation->setPenalty((float)$row['penalty']);
        }

        // Met à jour le statut si nécessaire
        if ($row['status'] !== 'PENDING') {
            match ($row['status']) {
                'CONFIRMED' => $reservation->confirm(),
                'CANCELED'  => $reservation->cancel(),
                'COMPLETED' => $reservation->complete(),
                default     => null,
            };
        }

        return $reservation;
    }
}
