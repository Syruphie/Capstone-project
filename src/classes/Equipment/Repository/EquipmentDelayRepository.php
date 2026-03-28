<?php
declare(strict_types=1);

class EquipmentDelayRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function logDelay(int $equipmentId, string $delayStart, int $delayDuration, string $reason, int $loggedBy): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO equipment_delays (equipment_id, delay_start, delay_duration, reason, logged_by) VALUES (?, ?, ?, ?, ?)'
        );

        return $stmt->execute([$equipmentId, $delayStart, $delayDuration, $reason, $loggedBy]);
    }

    public function getDelayHistory(int $equipmentId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM equipment_delays WHERE equipment_id = ? ORDER BY delay_start DESC');
        $stmt->execute([$equipmentId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

