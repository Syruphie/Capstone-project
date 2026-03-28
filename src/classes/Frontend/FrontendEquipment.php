<?php
declare(strict_types=1);

require_once __DIR__ . '/../Equipment/Repository/EquipmentRepository.php';
require_once __DIR__ . '/../Equipment/Repository/EquipmentDelayRepository.php';
require_once __DIR__ . '/../Equipment/Service/EquipmentService.php';

class FrontendEquipment
{
    private EquipmentService $service;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();
        $this->service = new EquipmentService(new EquipmentRepository($db), new EquipmentDelayRepository($db));
    }

    public function addEquipment(string $name, string $equipmentType, int $processingTime, int $warmupTime, int $breakInterval, int $breakDuration, int $dailyCapacity, bool $isAvailable = true, ?string $lastMaintenance = null): int
    {
        return $this->service->addEquipment($name, $equipmentType, $processingTime, $warmupTime, $breakInterval, $breakDuration, $dailyCapacity, $isAvailable, $lastMaintenance);
    }

    public function getEquipmentById(int $equipmentId): ?array
    {
        return $this->service->getEquipmentById($equipmentId);
    }

    public function getAllEquipment(bool $availableOnly = false): array
    {
        return $this->service->getAllEquipment($availableOnly);
    }

    public function getAllEquipmentWithStats(): array
    {
        return $this->service->getAllEquipmentWithStats();
    }
}

