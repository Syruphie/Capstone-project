<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/EquipmentMapper.php';

class EquipmentRepository
{
    private PDO $db;
    private EquipmentMapper $mapper;

    public function __construct(PDO $db, ?EquipmentMapper $mapper = null)
    {
        $this->db = $db;
        $this->mapper = $mapper ?? new EquipmentMapper();
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
        $stmt = $this->db->prepare(
            'INSERT INTO equipment (name, equipment_type, processing_time_per_sample, warmup_time, break_interval, break_duration, daily_capacity, is_available, last_maintenance)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $name,
            $equipmentType,
            $processingTime,
            $warmupTime,
            $breakInterval,
            $breakDuration,
            $dailyCapacity,
            $isAvailable ? 1 : 0,
            $lastMaintenance,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getEquipmentById(int $equipmentId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM equipment WHERE id = ? LIMIT 1');
        $stmt->execute([$equipmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getEquipmentEntityById(int $equipmentId): ?Equipment
    {
        $row = $this->getEquipmentById($equipmentId);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function updateEquipment(int $equipmentId, array $data): bool
    {
        $allowed = [
            'name',
            'equipment_type',
            'processing_time_per_sample',
            'warmup_time',
            'break_interval',
            'break_duration',
            'daily_capacity',
            'is_available',
            'last_maintenance',
        ];

        $sets = [];
        $params = [];

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $sets[] = "{$key} = ?";
            $params[] = $key === 'is_available' ? ($data[$key] ? 1 : 0) : $data[$key];
        }

        if ($sets === []) {
            return false;
        }

        $params[] = $equipmentId;
        $stmt = $this->db->prepare('UPDATE equipment SET ' . implode(', ', $sets) . ' WHERE id = ?');

        return $stmt->execute($params);
    }

    public function deleteEquipment(int $equipmentId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM equipment WHERE id = ?');

        return $stmt->execute([$equipmentId]);
    }

    public function getAllEquipment(bool $availableOnly = false): array
    {
        $sql = $availableOnly
            ? 'SELECT * FROM equipment WHERE is_available = 1'
            : 'SELECT * FROM equipment';

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setAvailability(int $equipmentId, bool $isAvailable): bool
    {
        return $this->updateEquipment($equipmentId, ['is_available' => $isAvailable]);
    }

    public function getUtilizationRate(int $equipmentId, string $startDate, string $endDate): float
    {
        $eq = $this->getEquipmentById($equipmentId);
        if ($eq === null || (int)$eq['daily_capacity'] <= 0) {
            return 0.0;
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(DISTINCT q.order_id) AS used
             FROM queue q
             WHERE q.equipment_id = ? AND q.scheduled_start IS NOT NULL AND q.scheduled_end IS NOT NULL
             AND q.scheduled_start <= ? AND q.scheduled_end >= ?'
        );
        $stmt->execute([$equipmentId, $endDate, $startDate]);
        $used = (int)($stmt->fetch(PDO::FETCH_ASSOC)['used'] ?? 0);

        $days = max(1, (int)floor((strtotime($endDate) - strtotime($startDate)) / 86400));
        $capacity = (int)$eq['daily_capacity'] * $days;

        if ($capacity <= 0) {
            return 0.0;
        }

        return round((100 * $used) / $capacity, 2);
    }
}

