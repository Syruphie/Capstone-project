<?php
declare(strict_types=1);

require_once __DIR__ . '/../Queue/Repository/QueueRepository.php';
require_once __DIR__ . '/../Queue/Service/QueueService.php';
require_once __DIR__ . '/../Queue/Service/QueuePositionService.php';
require_once __DIR__ . '/../Queue/Service/QueueSchedulingService.php';
require_once __DIR__ . '/../Queue/Service/QueueStatisticsService.php';

class FrontendQueue
{
    private QueueRepository $repo;
    private QueueService $service;
    private QueuePositionService $positionService;
    private QueueSchedulingService $schedulingService;
    private QueueStatisticsService $statsService;

    public function __construct()
    {
        $this->repo = new QueueRepository(Database::getInstance()->getConnection());
        $this->service = new QueueService($this->repo);
        $this->positionService = new QueuePositionService($this->repo);
        $this->schedulingService = new QueueSchedulingService($this->repo);
        $this->statsService = new QueueStatisticsService($this->repo);
    }

    public function getStandardQueue(): array
    {
        return array_map(static fn(QueueEntry $entry): array => [
            'id' => $entry->getId(),
            'order_id' => $entry->getOrderId(),
            'equipment_id' => $entry->getEquipmentId(),
            'position' => $entry->getPosition(),
            'queue_type' => $entry->getQueueType(),
            'scheduled_start' => $entry->getScheduledStart(),
            'scheduled_end' => $entry->getScheduledEnd(),
            'actual_start' => $entry->getActualStart(),
            'actual_end' => $entry->getActualEnd(),
        ], $this->repo->getByQueueType(QueueType::STANDARD));
    }

    public function getQueueById(int $queueId): ?array
    {
        $entry = $this->repo->getById($queueId);
        if ($entry === null) {
            return null;
        }

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
        ];
    }

    public function getLastScheduledEnd(int $equipmentId): ?string
    {
        return $this->repo->getLastScheduledEnd($equipmentId);
    }

    public function addToQueueScheduled(int $orderId, int $equipmentId, string $priority, string $start, string $end): int
    {
        $queueType = $priority === 'priority' ? QueueType::PRIORITY : QueueType::STANDARD;

        return $this->service->addScheduledToQueue($orderId, $equipmentId, $queueType, $start, $end);
    }

    public function updateSchedule(int $queueId, string $start, string $end): bool
    {
        return $this->schedulingService->scheduleQueueEntry($queueId, $start, $end);
    }

    public function updatePosition(int $queueId, int $newPosition): bool
    {
        return $this->positionService->updatePosition($queueId, $newPosition);
    }

    public function getQueueStatistics(string $from, string $to): array
    {
        return $this->statsService->getQueueStatistics($from, $to);
    }

    public function getQueueEntriesForReport(string $from, string $to): array
    {
        return $this->repo->getQueueEntriesForReport($from, $to);
    }

    public function getCalendarData(): array
    {
        return $this->repo->getCalendarData();
    }

    public function getQueueByEquipment(int $equipmentId, string $from, string $to): array
    {
        return $this->repo->getQueueByEquipmentAndDateRange($equipmentId, $from, $to);
    }
}

