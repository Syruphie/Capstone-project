<?php
declare(strict_types=1);

/**
 * Class QueueService
 *
 * Provides high-level queue operations and orchestrates core use cases.
 *
 * This service coordinates between the repository and other domain services
 * to perform actions such as adding/removing queue entries and handling
 * priority conversions.
 *
 * Responsibilities:
 * - Add entries to the queue (standard and scheduled)
 * - Remove entries from the queue
 * - Convert entries between queue types (e.g., standard → priority)
 * - Coordinate with position and scheduling services when needed
 *
 * Non-Responsibilities:
 * - No direct SQL queries (delegates to repository)
 * - No detailed scheduling algorithms
 * - No reporting/statistics logic
 *
 * Design Notes:
 * - Acts as the main entry point for queue-related operations from controllers
 * - Should enforce validation and business rules at a high level
 */

require_once __DIR__ . '/../Repository/QueueRepository.php';
require_once __DIR__ . '/../Entity/QueueEntry.php';
require_once __DIR__ . '/../Support/QueueType.php';
require_once __DIR__ . '/../../Support/DateRangeValidator.php';

class QueueService
{
    private QueueRepository $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function addToQueue(int $orderId, int $equipmentId, string $queueType = QueueType::STANDARD): int
    {
        $this->validateQueueType($queueType);

        $position = $this->queueRepository->getNextPositionByType($queueType);

        $entry = new QueueEntry(
                null,
                $orderId,
                $equipmentId,
                $position,
                $queueType
        );

        $id = $this->queueRepository->insert($entry);
        $entry->setId($id);

        return $id;
    }

    public function addScheduledToQueue(
            int $orderId,
            int $equipmentId,
            string $queueType,
            string $scheduledStart,
            string $scheduledEnd
    ): int
    {
        DateRangeValidator::validate($scheduledStart, $scheduledEnd);
        $this->validateQueueType($queueType);

        $position = $this->queueRepository->getNextPositionByType($queueType);

        $entry = new QueueEntry(
                null,
                $orderId,
                $equipmentId,
                $position,
                $queueType,
                $scheduledStart,
                $scheduledEnd
        );

        $id = $this->queueRepository->insert($entry);
        $entry->setId($id);

        return $id;
    }

    public function removeFromQueue(int $queueId): bool
    {
        $existing = $this->queueRepository->getById($queueId);

        if (!$existing) {
            return false;
        }

        return $this->queueRepository->deleteById($queueId);
    }

    public function findById(int $queueId): ?QueueEntry
    {
        return $this->queueRepository->getById($queueId);
    }

    public function findByOrderId(int $orderId): ?QueueEntry
    {
        return $this->queueRepository->getByOrderId($orderId);
    }

    private function validateQueueType(string $queueType): void
    {
        if (!QueueType::isValid($queueType)) {
            throw new InvalidArgumentException("Invalid queue type: {$queueType}");
        }
    }
}