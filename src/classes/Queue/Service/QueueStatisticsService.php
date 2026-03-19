<?php
declare(strict_types=1);

/**
 * Class QueueStatisticsService
 *
 * Provides statistical insights and metrics for queue data.
 *
 * This service calculates aggregate metrics such as queue length,
 * average wait times, and summary statistics used in dashboards
 * or reports.
 *
 * Responsibilities:
 * - Calculate queue lengths (standard, priority, total)
 * - Compute average wait times
 * - Provide summarized queue statistics for reporting
 *
 * Non-Responsibilities:
 * - No queue mutation (no inserts/updates/deletes)
 * - No scheduling or position logic
 *
 * Design Notes:
 * - Read-only service focused on analytics
 * - May depend on repository or query service for data access
 */

require_once __DIR__ . '/../Repository/QueueRepository.php';
require_once __DIR__ . '/../Support/QueueType.php';

class QueueStatisticsService
{
    private QueueRepository $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function getQueueLength(?string $queueType = null): int
    {
        return $this->queueRepository->getActiveEntryCount($queueType);
    }

    public function getQueueStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $standard = $this->getQueueLength(QueueType::STANDARD);
        $priority = $this->getQueueLength(QueueType::PRIORITY);
        $averageWait = $this->queueRepository->getAverageWaitTime(null, $startDate, $endDate);

        return [
                'standard_queue_length' => $standard,
                'priority_queue_length' => $priority,
                'total_in_queue' => $standard + $priority,
                'average_wait_minutes' => $averageWait,
                'from' => $startDate,
                'to' => $endDate,
        ];
    }
}