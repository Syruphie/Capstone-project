<?php
declare(strict_types=1);

/**
 * Class QueueRepository
 *
 * Handles all direct database interactions for queue entries.
 *
 * This class is responsible for all persistence and retrieval logic
 * related to the `queue` table and its associated data. It acts as the
 * single source of truth for database access for queue-related operations.
 *
 * Responsibilities:
 * - Insert, update, and delete queue entries
 * - Retrieve queue entries by ID, order, type, or other filters
 * - Execute all read queries, including joins for related data (e.g., orders, equipment)
 * - Provide query methods for UI, reporting, and calendar views when needed
 * - Execute utility queries (e.g., next position, counts, last scheduled end)
 *
 * Non-Responsibilities:
 * - No business logic (e.g., queue movement rules, scheduling strategies)
 * - No orchestration of multistep workflows
 * - No validation beyond basic data integrity
 *
 * Design Notes:
 * - Acts as the centralized data access layer for queue operations
 * - May contain both simple queries and more complex joined queries
 * - Should remain focused on SQL execution and data retrieval/persistence
 * - Can be reused across multiple services and controllers
 */

require_once __DIR__ . '/../Support/QueueMapper.php';
require_once __DIR__ . '/../Entity/QueueEntry.php';
require_once __DIR__ . '/../Support/QueueType.php';
require_once __DIR__ . '/../../Support/DateRangeValidator.php';

class QueueRepository
{
    private PDO $db;
    private QueueMapper $mapper;

    public function __construct(PDO $db, ?QueueMapper $mapper = null)
    {
        $this->db = $db;
        $this->mapper = $mapper ?? new QueueMapper();
    }

    public function getById(int $id): ?QueueEntry
    {
        $stmt = $this->db->prepare("SELECT * FROM queue WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function getByOrderId(int $orderId): ?QueueEntry
    {
        $stmt = $this->db->prepare("SELECT * FROM queue WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function insert(QueueEntry $entry): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO queue (
                order_id,
                equipment_id,
                position,
                queue_type,
                scheduled_start,
                scheduled_end,
                actual_start,
                actual_end
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $entry->getOrderId(),
            $entry->getEquipmentId(),
            $entry->getPosition(),
            $entry->getQueueType(),
            $entry->getScheduledStart(),
            $entry->getScheduledEnd(),
            $entry->getActualStart(),
            $entry->getActualEnd(),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(QueueEntry $entry): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE queue
             SET equipment_id = ?,
                 position = ?,
                 queue_type = ?,
                 scheduled_start = ?,
                 scheduled_end = ?,
                 actual_start = ?,
                 actual_end = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([
            $entry->getEquipmentId(),
            $entry->getPosition(),
            $entry->getQueueType(),
            $entry->getScheduledStart(),
            $entry->getScheduledEnd(),
            $entry->getActualStart(),
            $entry->getActualEnd(),
            $entry->getId(),
        ]);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM queue WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getNextPositionByType(string $queueType): int
    {
        if (!QueueType::isValid($queueType)) {
            throw new InvalidArgumentException("Invalid queue type: {$queueType}");
        }

        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(position), 0) + 1 AS next_position
             FROM queue
             WHERE queue_type = ?"
        );
        $stmt->execute([$queueType]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$row['next_position'];
    }

    public function getByQueueType(string $queueType, ?int $limit = null): array
    {
        if (!QueueType::isValid($queueType)) {
            throw new InvalidArgumentException("Invalid queue type: {$queueType}");
        }

        if ($limit === 0) {
            return [];
        }

        $sql = "SELECT * FROM queue WHERE queue_type = ? ORDER BY position ASC";
        $params = [$queueType];

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => $this->mapper->mapRowToEntity($row), $rows);
    }

    public function getLastScheduledEnd(int $equipmentId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT scheduled_end
             FROM queue
             WHERE equipment_id = ?
               AND scheduled_end IS NOT NULL
             ORDER BY scheduled_end DESC
             LIMIT 1"
        );
        $stmt->execute([$equipmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['scheduled_end'] : null;
    }

    public function getActiveEntryCount(?string $queueType = null): int
    {
        if ($queueType !== null && !QueueType::isValid($queueType)) {
            throw new InvalidArgumentException("Invalid queue type: {$queueType}");
        }

        $sql = "SELECT COUNT(*) AS cnt
                FROM queue q
                JOIN orders o ON q.order_id = o.id
                WHERE o.status NOT IN ('results_available', 'completed')";
        $params = [];

        if ($queueType !== null) {
            $sql .= " AND q.queue_type = ?";
            $params[] = $queueType;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$row['cnt'];
    }

    public function getAllQueueEntries(): array
    {
        $stmt = $this->db->prepare(
            "SELECT q.*, o.order_number, o.status AS order_status, o.priority, o.estimated_completion
             FROM queue q
             JOIN orders o ON q.order_id = o.id
             ORDER BY q.queue_type DESC, q.position ASC"
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQueueByEquipmentAndDateRange(int $equipmentId, ?string $fromDate = null, ?string $toDate = null): array
    {
        if ($fromDate !== null && $toDate !== null) {
            DateRangeValidator::validate($fromDate, $toDate);
        }

        $sql = "SELECT q.*, o.order_number, o.status AS order_status
                FROM queue q
                JOIN orders o ON q.order_id = o.id
                WHERE q.equipment_id = ?
                  AND q.scheduled_start IS NOT NULL
                  AND q.scheduled_end IS NOT NULL";
        $params = [$equipmentId];

        if ($fromDate !== null) {
            $sql .= " AND q.scheduled_end >= ?";
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= " AND q.scheduled_start <= ?";
            $params[] = $toDate;
        }

        $sql .= " ORDER BY q.scheduled_start ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCalendarData(): array
    {
        $stmt = $this->db->prepare(
            "SELECT q.id AS queue_id, q.order_id, q.equipment_id, q.position, q.scheduled_start, q.scheduled_end,
                    q.queue_type, o.order_number, o.status AS order_status, o.priority, o.created_at, o.estimated_completion, o.order_note,
                    u.full_name AS customer_name, u.company_name,
                    e.name AS equipment_name,
                    (SELECT GROUP_CONCAT(DISTINCT s.sample_type ORDER BY s.sample_type)
                     FROM samples s
                     WHERE s.order_id = o.id) AS sample_types
             FROM queue q
             JOIN orders o ON q.order_id = o.id
             JOIN users u ON o.customer_id = u.id
             LEFT JOIN equipment e ON q.equipment_id = e.id
             WHERE o.status NOT IN ('results_available', 'completed')
             ORDER BY q.queue_type DESC, q.position ASC"
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQueueEntriesForReport(?string $startDate = null, ?string $endDate = null): array
    {
        if ($startDate !== null && $endDate !== null) {
            DateRangeValidator::validate($startDate, $endDate);
        }

        $sql = "SELECT q.*, o.order_number, o.status AS order_status, o.created_at AS order_created, e.name AS equipment_name
                FROM queue q
                JOIN orders o ON q.order_id = o.id
                LEFT JOIN equipment e ON q.equipment_id = e.id
                WHERE 1=1";
        $params = [];

        if ($startDate !== null) {
            $sql .= " AND q.scheduled_start >= ?";
            $params[] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND q.scheduled_end <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY q.scheduled_start ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }

    public function clearPosition(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE queue SET position = -1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function shiftSurroundingRowsUp(string $queueType, int $oldPosition, int $newPosition): void
    {
        $stmt = $this->db->prepare("UPDATE queue SET position = position - 1, updated_at = NOW() WHERE queue_type = ? AND position > ? AND position <= ?");
        $stmt->execute([$queueType, $oldPosition, $newPosition]);
    }

    public function shiftSurroundingRowsDown(string $queueType, int $oldPosition, int $newPosition): void
    {
        $stmt = $this->db->prepare(
            "UPDATE queue SET position = position + 1, updated_at = NOW() WHERE queue_type = ? AND position >= ? AND position < ?"
        );
        $stmt->execute([$queueType, $newPosition, $oldPosition]);
    }

    public function setPosition(int $newPosition, int $queueId): void
    {
        $stmt = $this->db->prepare("UPDATE queue SET position = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newPosition, $queueId]);
    }

    public function getAverageWaitTime(?string $queueType = null, ?string $startDate = null, ?string $endDate = null): float
    {
        if ($queueType !== null && !QueueType::isValid($queueType)) {
            throw new InvalidArgumentException("Invalid queue type: $queueType");
        }

        if ($startDate !== null && $endDate !== null) {
            DateRangeValidator::validate($startDate, $endDate);
        }

        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, o.created_at, q.scheduled_start)) AS avg_mins
                FROM queue q
                JOIN orders o ON q.order_id = o.id
                WHERE q.scheduled_start IS NOT NULL
                  AND o.created_at IS NOT NULL";
        $params = [];

        if ($queueType !== null) {
            $sql .= " AND q.queue_type = ?";
            $params[] = $queueType;
        }

        if ($startDate !== null) {
            $sql .= " AND q.scheduled_start >= ?";
            $params[] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND q.scheduled_start <= ?";
            $params[] = $endDate;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row && $row['avg_mins'] !== null ? round((float)$row['avg_mins'], 1) : 0.0;
    }
}