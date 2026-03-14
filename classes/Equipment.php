<?php
require_once __DIR__ . '/../config/database.php';

class Equipment {
    private $db;
    
    // Properties
    private $id;
    private $name;
    private $equipmentType;
    private $processingTimePerSample;
    private $warmupTime;
    private $breakInterval;
    private $breakDuration;
    private $dailyCapacity;
    private $isAvailable;
    private $lastMaintenance;
    private $createdAt;
    private $updatedAt;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Equipment Management Methods
    public function addEquipment($name, $equipmentType, $processingTime, $warmupTime, $breakInterval, $breakDuration, $dailyCapacity, $isAvailable = true, $lastMaintenance = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment (name, equipment_type, processing_time_per_sample, warmup_time, break_interval, break_duration, daily_capacity, is_available, last_maintenance) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $name,
            $equipmentType,
            (int) $processingTime,
            (int) $warmupTime,
            (int) $breakInterval,
            (int) $breakDuration,
            (int) $dailyCapacity,
            $isAvailable ? 1 : 0,
            $lastMaintenance ?: null
        ]) ? $this->db->lastInsertId() : false;
    }

    public function getEquipmentById($equipmentId) {
        $stmt = $this->db->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->execute([$equipmentId]);
        return $stmt->fetch();
    }

    public function updateEquipment($equipmentId, $data) {
        $allowed = ['name', 'equipment_type', 'processing_time_per_sample', 'warmup_time', 'break_interval', 'break_duration', 'daily_capacity', 'is_available', 'last_maintenance'];
        $sets = [];
        $params = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $sets[] = "{$key} = ?";
                $params[] = $key === 'is_available' ? ($data[$key] ? 1 : 0) : $data[$key];
            }
        }
        if (empty($sets)) return false;
        $params[] = (int) $equipmentId;
        $stmt = $this->db->prepare("UPDATE equipment SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function deleteEquipment($equipmentId) {
        $stmt = $this->db->prepare("DELETE FROM equipment WHERE id = ?");
        return $stmt->execute([(int) $equipmentId]);
    }

    public function getAllEquipment($availableOnly = false) {
        if ($availableOnly) {
            $stmt = $this->db->prepare("SELECT * FROM equipment WHERE is_available = 1");
        } else {
            $stmt = $this->db->prepare("SELECT * FROM equipment");
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Equipment Availability Methods
    public function setAvailability($equipmentId, $isAvailable) {
        // Method signature for setting equipment availability
    }

    public function checkAvailability($equipmentId, $startTime, $endTime) {
        // Method signature for checking if equipment is available during time period
    }

    public function getAvailableEquipment($equipmentType = null) {
        // Method signature for retrieving available equipment, optionally by type
    }

    // Equipment Scheduling Methods
    public function scheduleEquipment($equipmentId, $sampleId, $startTime, $duration) {
        // Method signature for scheduling equipment for a sample
    }

    public function calculateProcessingTime($equipmentId, $sampleCount) {
        // Method signature for calculating total processing time including breaks
    }

    public function getEquipmentSchedule($equipmentId, $date = null) {
        // Method signature for retrieving equipment schedule for a date
    }

    public function getNextAvailableSlot($equipmentId) {
        // Method signature for finding next available time slot
    }

    // Equipment Capacity Methods
    public function calculateDailyCapacity($equipmentId) {
        // Method signature for calculating daily capacity based on specs
    }

    public function getRemainingCapacity($equipmentId, $date = null) {
        // Method signature for calculating remaining capacity for a date
    }

    public function isAtCapacity($equipmentId, $date = null) {
        // Method signature for checking if equipment is at capacity
    }

    // Equipment Maintenance Methods
    public function logMaintenance($equipmentId, $maintenanceType, $details) {
        // Method signature for logging maintenance activity
    }

    public function getMaintenanceHistory($equipmentId) {
        // Method signature for retrieving maintenance history
    }

    public function scheduleMaintenanceEquipmentId($scheduledDate) {
        // Method signature for scheduling maintenance
    }

    // Equipment Delay Methods
    public function logDelay($equipmentId, $delayStart, $delayDuration, $reason, $loggedBy) {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_delays (equipment_id, delay_start, delay_duration, reason, logged_by) 
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$equipmentId, $delayStart, $delayDuration, $reason, $loggedBy]);
    }

    public function getDelayHistory($equipmentId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM equipment_delays WHERE equipment_id = ? ORDER BY delay_start DESC"
        );
        $stmt->execute([(int) $equipmentId]);
        return $stmt->fetchAll();
    }

    public function calculateDelayImpact($equipmentId, $delayDuration) {
        // Method signature for calculating impact of delay on schedule
    }

    // Equipment Statistics Methods
    public function getUtilizationRate($equipmentId, $startDate, $endDate) {
        $eq = $this->getEquipmentById($equipmentId);
        if (!$eq || !$eq['daily_capacity']) return 0;
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT q.order_id) as used 
             FROM queue q 
             WHERE q.equipment_id = ? AND q.scheduled_start IS NOT NULL AND q.scheduled_end IS NOT NULL 
             AND q.scheduled_start <= ? AND q.scheduled_end >= ?"
        );
        $stmt->execute([(int) $equipmentId, $endDate, $startDate]);
        $used = (int) $stmt->fetch()['used'];
        $days = max(1, (strtotime($endDate) - strtotime($startDate)) / 86400);
        $capacity = (int) $eq['daily_capacity'] * $days;
        return $capacity > 0 ? round(100 * $used / $capacity, 2) : 0;
    }

    public function getEquipmentStatistics($equipmentId) {
        $eq = $this->getEquipmentById($equipmentId);
        if (!$eq) return null;
        $delays = $this->getDelayHistory($equipmentId);
        return [
            'equipment' => $eq,
            'delay_count' => count($delays),
            'delays' => $delays,
        ];
    }

    public function getAllEquipmentWithStats() {
        $list = $this->getAllEquipment(false);
        $result = [];
        foreach ($list as $eq) {
            $delays = $this->getDelayHistory($eq['id']);
            $result[] = array_merge($eq, ['delay_count' => count($delays), 'delays' => $delays]);
        }
        return $result;
    }

    public function getAverageProcessingTime($equipmentId) {
        // Method signature for calculating average processing time
    }

    // Getters and Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getEquipmentType() {
        return $this->equipmentType;
    }

    public function setEquipmentType($equipmentType) {
        $this->equipmentType = $equipmentType;
    }

    public function getProcessingTimePerSample() {
        return $this->processingTimePerSample;
    }

    public function setProcessingTimePerSample($processingTimePerSample) {
        $this->processingTimePerSample = $processingTimePerSample;
    }

    public function getWarmupTime() {
        return $this->warmupTime;
    }

    public function setWarmupTime($warmupTime) {
        $this->warmupTime = $warmupTime;
    }

    public function getBreakInterval() {
        return $this->breakInterval;
    }

    public function setBreakInterval($breakInterval) {
        $this->breakInterval = $breakInterval;
    }

    public function getBreakDuration() {
        return $this->breakDuration;
    }

    public function setBreakDuration($breakDuration) {
        $this->breakDuration = $breakDuration;
    }

    public function getDailyCapacity() {
        return $this->dailyCapacity;
    }

    public function setDailyCapacity($dailyCapacity) {
        $this->dailyCapacity = $dailyCapacity;
    }

    public function getIsAvailable() {
        return $this->isAvailable;
    }

    public function setIsAvailable($isAvailable) {
        $this->isAvailable = $isAvailable;
    }

    public function getLastMaintenance() {
        return $this->lastMaintenance;
    }

    public function setLastMaintenance($lastMaintenance) {
        $this->lastMaintenance = $lastMaintenance;
    }
}
