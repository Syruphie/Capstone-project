<?php
declare(strict_types=1);

/**
 * Class QueueSchedulingService
 *
 * Manages scheduling logic for queue entries.
 *
 * This service is responsible for assigning, updating, and recalculating
 * scheduled start/end times for queue entries, as well as handling delays
 * and scheduling adjustments.
 *
 * Responsibilities:
 * - Assign or update scheduled time windows
 * - Recalculate schedules based on queue position or changes
 * - Estimate wait times for queue entries
 * - Adjust schedules due to delays or equipment constraints
 * - Optimize or redistribute queue schedules
 *
 * Non-Responsibilities:
 * - No direct database access (delegates to repository)
 * - No queue position logic (handled separately)
 * - No reporting/statistics
 *
 * Design Notes:
 * - Encapsulates all time-based logic
 * - Should remain independent of UI concerns
 */

require_once __DIR__ . '/../Repository/QueueRepository.php';
require_once __DIR__ . '/../../Support/DateRangeValidator.php';

class QueueSchedulingService
{
    private QueueRepository $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function scheduleQueueEntry(int $queueId, string $scheduledStart, string $scheduledEnd): bool
    {
        DateRangeValidator::validate($scheduledStart, $scheduledEnd);
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry) {
            return false;
        }

        $entry->setScheduledStart($scheduledStart);
        $entry->setScheduledEnd($scheduledEnd);

        return $this->queueRepository->update($entry);
    }

    public function getEstimatedWaitTime(int $queueId): ?int
    {
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry || !$entry->getScheduledStart()) {
            return null;
        }

        $scheduled = strtotime($entry->getScheduledStart());
        $now = time();

        return max(0, (int)round(($scheduled - $now) / 60));
    }

    public function recalculateSchedule(string $queueType, int $startingPosition = 1): void
    {
        // TODO: implement scheduling recalculation strategy
    }

    public function adjustForDelay(int $equipmentId, int $delayMinutes): void
    {
        // TODO: implement delay handling
    }

    public function redistributeQueue(int $equipmentId): void
    {
        // TODO: implement redistribution logic
    }

    public function optimizeQueue(string $queueType): void
    {
        // TODO: implement optimization logic
    }
}