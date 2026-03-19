<?php
declare(strict_types=1);

/**
 * Class QueueMapper
 *
 * Handles transformation between database rows and QueueEntry objects.
 *
 * This class is responsible for converting raw associative arrays returned
 * from database queries into QueueEntry entities, and optionally converting
 * entities back into array form for persistence.
 *
 * Responsibilities:
 * - Map database result arrays → QueueEntry objects
 * - Map QueueEntry objects → database-ready arrays (for inserts/updates)
 * - Centralize data transformation logic to avoid duplication
 *
 * Non-Responsibilities:
 * - No database queries
 * - No business logic (e.g., scheduling, positioning)
 * - No validation or orchestration
 *
 * Design Notes:
 * - Keeps repository clean by separating mapping concerns
 * - Ensures consistent object structure across the application
 * - Useful if transitioning away from raw associative arrays
 *
 * Example Usage:
 * - Repository fetches row → passes to QueueMapper → returns QueueEntry
 * - Service creates QueueEntry → passes to mapper → repository persists
 */

require_once __DIR__ . '/../Entity/QueueEntry.php';

class QueueMapper
{
    public function mapRowToEntity(array $row): QueueEntry
    {
        return new QueueEntry(
                isset($row['id']) ? (int)$row['id'] : null,
                (int)$row['order_id'],
                (int)$row['equipment_id'],
                (int)$row['position'],
                (string)$row['queue_type'],
                $row['scheduled_start'] ?? null,
                $row['scheduled_end'] ?? null,
                $row['actual_start'] ?? null,
                $row['actual_end'] ?? null,
                $row['created_at'] ?? null,
                $row['updated_at'] ?? null
        );
    }

    public function mapEntityToArray(QueueEntry $entry): array
    {
        return [
                'id' => $entry->getId(),
                'order_id' => $entry->getOrderId(),
                'equipment_id' => $entry->getEquipmentId(),
                'position' => $entry->getPosition(),
                'queue_type' => $entry->getQueueType(),
                'scheduled_start' => $entry->getScheduledStart(),
                'scheduled_end' => $entry->getScheduledEnd(),
                'actual_start' => $entry->getActualStart(),
                'actual_end' => $entry->getActualEnd(),
                'created_at' => $entry->getCreatedAt(),
                'updated_at' => $entry->getUpdatedAt(),
        ];
    }
}