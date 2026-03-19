<?php
declare(strict_types=1);

/**
 * Class QueuePositionService
 *
 * Handles all logic related to queue positioning and reordering.
 *
 * This service is responsible for maintaining correct ordering
 * of queue entries when items are moved, inserted, or removed.
 *
 * Responsibilities:
 * - Update position of a queue entry
 * - Move entries up or down in the queue
 * - Reorder queue after removals or changes
 *
 * Non-Responsibilities:
 * - No scheduling logic
 * - No reporting/statistics
 * - No direct SQL (delegates to repository)
 *
 * Design Notes:
 * - Critical for maintaining queue integrity
 * - Should use transactions for multistep updates
 */

require_once __DIR__ . '/../Repository/QueueRepository.php';

class QueuePositionService
{
    private QueueRepository $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function updatePosition(int $queueId, int $newPosition): bool
    {
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry) {
            return false;
        }

        $oldPosition = $entry->getPosition();
        $queueType = $entry->getQueueType();

        if ($oldPosition === $newPosition) {
            return true;
        }

        try {
            $this->queueRepository->beginTransaction();
            $this->queueRepository->clearPosition($queueId);

            if ($newPosition > $oldPosition) {
                $this->queueRepository->shiftSurroundingRowsUp($queueType, $oldPosition, $newPosition);
            } else {
                $this->queueRepository->shiftSurroundingRowsDown($queueType, $oldPosition, $newPosition);
            }

            $this->queueRepository->setPosition($newPosition, $queueId);

            $this->queueRepository->commit();
            return true;
        } catch (Throwable $e) {
            $this->queueRepository->rollBack();
            return false;
        }
    }

    public function moveUp(int $queueId): bool
    {
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry || $entry->getPosition() <= 1) {
            return false;
        }

        return $this->updatePosition($queueId, $entry->getPosition() - 1);
    }

    public function moveDown(int $queueId): bool
    {
        $entry = $this->queueRepository->getById($queueId);

        if (!$entry) {
            return false;
        }

        return $this->updatePosition($queueId, $entry->getPosition() + 1);
    }

    public function getPosition(int $queueId): ?int
    {
        $entry = $this->queueRepository->getById($queueId);
        return $entry ? $entry->getPosition() : null;
    }

    public function reorderQueue(string $queueType): void
    {
        $entries = $this->queueRepository->getByQueueType($queueType);

        $position = 1;
        foreach ($entries as $entry) {
            $entry->setPosition($position++);
            $this->queueRepository->update($entry);
        }
    }
}