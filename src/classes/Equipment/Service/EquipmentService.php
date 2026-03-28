<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repository/EquipmentRepository.php';
require_once __DIR__ . '/../Repository/EquipmentDelayRepository.php';
require_once __DIR__ . '/../../../../config/database.php';

class EquipmentService
{
    private EquipmentRepository $equipmentRepository;
    private EquipmentDelayRepository $equipmentDelayRepository;

    public function __construct(
        ?EquipmentRepository $equipmentRepository = null,
        ?EquipmentDelayRepository $equipmentDelayRepository = null
    )
    {
        if ($equipmentRepository !== null && $equipmentDelayRepository !== null) {
            $this->equipmentRepository = $equipmentRepository;
            $this->equipmentDelayRepository = $equipmentDelayRepository;
            return;
        }

        $db = Database::getInstance()->getConnection();
        $this->equipmentRepository = $equipmentRepository ?? new EquipmentRepository($db);
        $this->equipmentDelayRepository = $equipmentDelayRepository ?? new EquipmentDelayRepository($db);
    }

    public function addEquipment(
        string $name,
        string $equipmentType,
        int $processingTime,
        int $warmupTime,
        int $breakInterval,
        int $breakDuration,
        int $dailyCapacity,
        bool $isAvailable = true,
        ?string $lastMaintenance = null
    ): int
    {
        return $this->equipmentRepository->addEquipment(
            $name,
            $equipmentType,
            $processingTime,
            $warmupTime,
            $breakInterval,
            $breakDuration,
            $dailyCapacity,
            $isAvailable,
            $lastMaintenance
        );
    }

    public function getEquipmentById(int $equipmentId): ?array
    {
        return $this->equipmentRepository->getEquipmentById($equipmentId);
    }

    public function updateEquipment(int $equipmentId, array $data): bool
    {
        return $this->equipmentRepository->updateEquipment($equipmentId, $data);
    }

    public function deleteEquipment(int $equipmentId): bool
    {
        return $this->equipmentRepository->deleteEquipment($equipmentId);
    }

    public function getAllEquipment(bool $availableOnly = false): array
    {
        return $this->equipmentRepository->getAllEquipment($availableOnly);
    }

    public function setAvailability(int $equipmentId, bool $isAvailable): bool
    {
        return $this->equipmentRepository->setAvailability($equipmentId, $isAvailable);
    }

    public function getAvailableEquipment(?string $equipmentType = null): array
    {
        $rows = $this->equipmentRepository->getAllEquipment(true);

        if ($equipmentType === null) {
            return $rows;
        }

        return array_values(array_filter(
            $rows,
            static fn(array $row): bool => (string)$row['equipment_type'] === $equipmentType
        ));
    }

    public function logDelay(int $equipmentId, string $delayStart, int $delayDuration, string $reason, int $loggedBy): bool
    {
        return $this->equipmentDelayRepository->logDelay($equipmentId, $delayStart, $delayDuration, $reason, $loggedBy);
    }

    public function getDelayHistory(int $equipmentId): array
    {
        return $this->equipmentDelayRepository->getDelayHistory($equipmentId);
    }

    public function getUtilizationRate(int $equipmentId, string $startDate, string $endDate): float
    {
        return $this->equipmentRepository->getUtilizationRate($equipmentId, $startDate, $endDate);
    }

    public function getEquipmentStatistics(int $equipmentId): ?array
    {
        $equipment = $this->equipmentRepository->getEquipmentById($equipmentId);
        if ($equipment === null) {
            return null;
        }

        $delays = $this->equipmentDelayRepository->getDelayHistory($equipmentId);

        return [
            'equipment' => $equipment,
            'delay_count' => count($delays),
            'delays' => $delays,
        ];
    }

    public function getAllEquipmentWithStats(): array
    {
        $all = $this->equipmentRepository->getAllEquipment(false);

        return array_map(function (array $equipment): array {
            $delays = $this->equipmentDelayRepository->getDelayHistory((int)$equipment['id']);

            return array_merge($equipment, [
                'delay_count' => count($delays),
                'delays' => $delays,
            ]);
        }, $all);
    }

    public function checkAvailability(int $equipmentId, string $startTime, string $endTime): bool
    {
        // Placeholder for scheduling overlap checks during migration from legacy classes.
        return true;
    }

    public function scheduleEquipment(int $equipmentId, int $sampleId, string $startTime, int $duration): bool
    {
        // Placeholder for sample-to-equipment scheduling orchestration.
        return false;
    }

    public function calculateProcessingTime(int $equipmentId, int $sampleCount): int
    {
        $equipment = $this->equipmentRepository->getEquipmentById($equipmentId);
        if ($equipment === null) {
            return 0;
        }

        $sampleCount = max(0, $sampleCount);
        $base = (int)$equipment['processing_time_per_sample'] * $sampleCount;
        $warmup = (int)$equipment['warmup_time'];
        $interval = (int)$equipment['break_interval'];
        $breakDuration = (int)$equipment['break_duration'];

        if ($interval <= 0 || $breakDuration <= 0 || $sampleCount <= 1) {
            return $base + $warmup;
        }

        $breaks = intdiv(max(0, $sampleCount - 1), $interval);

        return $base + $warmup + ($breaks * $breakDuration);
    }
}

