<?php
declare(strict_types=1);

/**
 * Class QueueProcessingService
 *
 * Manages the lifecycle of queue entries during processing.
 *
 * This service is responsible for transitioning queue entries through
 * processing states (e.g., not started → in progress → completed) and
 * determining which entries should be processed next.
 *
 * Responsibilities:
 * - Mark queue entries as started (set actual start time)
 * - Mark queue entries as completed (set actual end time)
 * - Retrieve the next eligible queue entry for processing
 * - Determine whether a queue entry is currently being processed
 * - Handle priority processing logic (e.g., prioritizing priority queue items)
 * - Support operational workflows such as shift-based processing separation
 *
 * Non-Responsibilities:
 * - No scheduling logic (handled by QueueSchedulingService)
 * - No position/reordering logic (handled by QueuePositionService)
 * - No reporting/statistics
 * - No direct SQL queries (delegates to QueueRepository)
 *
 * Design Notes:
 * - Focuses strictly on runtime execution state of queue entries
 * - Should enforce valid state transitions (e.g., cannot complete before starting)
 * - May coordinate with QueueService and QueueSchedulingService when needed
 */

require_once __DIR__ . '/../Repository/QueueRepository.php';
require_once __DIR__ . '/../Support/QueueType.php';

class QueueProcessingService
{
    private QueueRepository $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function startProcessing(int $queueId): bool
    {
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry) {
            return false;
        }

        $entry->setActualStart(date('Y-m-d H:i:s'));

        return $this->queueRepository->update($entry);
    }

    public function completeProcessing(int $queueId): bool
    {
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry) {
            return false;
        }

        if ($entry->getActualStart() === null) {
            return false;
        }

        $entry->setActualEnd(date('Y-m-d H:i:s'));

        return $this->queueRepository->update($entry);
    }

    public function isProcessing(int $queueId): bool
    {
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry) {
            return false;
        }

        return $entry->getActualStart() !== null && $entry->getActualEnd() === null;
    }

    public function getNextInQueue(string $queueType): ?QueueEntry
    {
        if (!QueueType::isValid($queueType)) {
            throw new InvalidArgumentException("Invalid queue type: {$queueType}");
        }

        $entries = $this->queueRepository->getByQueueType($queueType, 1);
        return $entries[0] ?? null;
    }
}