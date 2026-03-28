<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/Equipment.php';

class EquipmentMapper
{
    public function mapRowToEntity(array $row): Equipment
    {
        $entity = new Equipment();
        $entity->setId(isset($row['id']) ? (int)$row['id'] : null);
        $entity->setName((string)($row['name'] ?? ''));
        $entity->setEquipmentType((string)($row['equipment_type'] ?? ''));
        $entity->setProcessingTimePerSample((int)($row['processing_time_per_sample'] ?? 0));
        $entity->setWarmupTime((int)($row['warmup_time'] ?? 0));
        $entity->setBreakInterval((int)($row['break_interval'] ?? 0));
        $entity->setBreakDuration((int)($row['break_duration'] ?? 0));
        $entity->setDailyCapacity((int)($row['daily_capacity'] ?? 0));
        $entity->setIsAvailable((bool)($row['is_available'] ?? true));
        $entity->setLastMaintenance($row['last_maintenance'] ?? null);
        $entity->setCreatedAt($row['created_at'] ?? null);
        $entity->setUpdatedAt($row['updated_at'] ?? null);

        return $entity;
    }
}

