<?php
require_once __DIR__ . '/../config/database.php';

class Queue {
    private $db;
    
    // Properties
    private $id;
    private $orderId;
    private $equipmentId;
    private $position;
    private $scheduledStart;
    private $scheduledEnd;
    private $actualStart;
    private $actualEnd;
    private $queueType;
    private $createdAt;
    private $updatedAt;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Queue Management Methods
    public function addToQueue($orderId, $equipmentId, $queueType = 'standard') {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM queue WHERE queue_type = ?"
        );
        $stmt->execute([$queueType]);
        $result = $stmt->fetch();
        $position = (int) $result['next_position'];

        $stmt = $this->db->prepare(
            "INSERT INTO queue (order_id, equipment_id, position, queue_type) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$orderId, $equipmentId, $position, $queueType]);
        return $this->db->lastInsertId();
    }

    /**
     * Add to queue with scheduled start/end. Used when auto-scheduling on approve.
     */
    public function addToQueueScheduled($orderId, $equipmentId, $queueType, $scheduledStart, $scheduledEnd) {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM queue WHERE queue_type = ?"
        );
        $stmt->execute([$queueType]);
        $result = $stmt->fetch();
        $position = (int) $result['next_position'];

        $stmt = $this->db->prepare(
            "INSERT INTO queue (order_id, equipment_id, position, queue_type, scheduled_start, scheduled_end) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$orderId, $equipmentId, $position, $queueType, $scheduledStart, $scheduledEnd]);
        return $this->db->lastInsertId();
    }

    public function removeFromQueue($queueId) {
        $stmt = $this->db->prepare("DELETE FROM queue WHERE id = ?");
        return $stmt->execute([$queueId]);
    }

    public function getQueueById($queueId) {
        $stmt = $this->db->prepare("SELECT * FROM queue WHERE id = ?");
        $stmt->execute([$queueId]);
        return $stmt->fetch();
    }

    public function getQueueByOrder($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM queue WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    // Queue Position Methods
    public function updatePosition($queueId, $newPosition) {
        $entry = $this->getQueueById($queueId);
        if (!$entry) return false;
        $queueType = $entry['queue_type'];
        $oldPos = (int) $entry['position'];
        $newPosition = (int) $newPosition;
        if ($oldPos === $newPosition) return true;
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE queue SET position = -1 WHERE id = ?");
            $stmt->execute([$queueId]);
            if ($newPosition > $oldPos) {
                $stmt = $this->db->prepare(
                    "UPDATE queue SET position = position - 1, updated_at = NOW() WHERE queue_type = ? AND position > ? AND position <= ?"
                );
                $stmt->execute([$queueType, $oldPos, $newPosition]);
            } else {
                $stmt = $this->db->prepare(
                    "UPDATE queue SET position = position + 1, updated_at = NOW() WHERE queue_type = ? AND position >= ? AND position < ?"
                );
                $stmt->execute([$queueType, $newPosition, $oldPos]);
            }
            $stmt = $this->db->prepare("UPDATE queue SET position = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newPosition, $queueId]);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getPosition($queueId) {
        // Method signature for getting current position in queue
    }

    public function moveUp($queueId) {
        // Method signature for moving an order up in the queue
    }

    public function moveDown($queueId) {
        // Method signature for moving an order down in the queue
    }

    public function reorderQueue($queueType) {
        // Method signature for reordering queue positions after removal
    }

    // Queue Retrieval Methods
    public function getStandardQueue($limit = null) {
        $sql = "SELECT q.*, o.order_number, o.customer_id 
                FROM queue q 
                JOIN orders o ON q.order_id = o.id 
                WHERE q.queue_type = 'standard' 
                ORDER BY q.position ASC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    public function getPriorityQueue($limit = null) {
        $sql = "SELECT q.*, o.order_number, o.customer_id 
                FROM queue q 
                JOIN orders o ON q.order_id = o.id 
                WHERE q.queue_type = 'priority' 
                ORDER BY q.position ASC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }

    public function getAllQueueEntries() {
        $stmt = $this->db->prepare(
            "SELECT q.*, o.order_number, o.status as order_status, o.priority, o.estimated_completion
             FROM queue q
             JOIN orders o ON q.order_id = o.id
             ORDER BY q.queue_type DESC, q.position ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getQueueByEquipment($equipmentId, $fromDate = null, $toDate = null) {
        $sql = "SELECT q.*, o.order_number, o.status as order_status
                FROM queue q
                JOIN orders o ON q.order_id = o.id
                WHERE q.equipment_id = ? AND q.scheduled_start IS NOT NULL AND q.scheduled_end IS NOT NULL";
        $params = [$equipmentId];
        if ($fromDate) { $sql .= " AND q.scheduled_end >= ?"; $params[] = $fromDate; }
        if ($toDate) { $sql .= " AND q.scheduled_start <= ?"; $params[] = $toDate; }
        $sql .= " ORDER BY q.scheduled_start ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Last scheduled_end for an equipment, or null if none.
     */
    public function getLastScheduledEnd($equipmentId) {
        $stmt = $this->db->prepare(
            "SELECT scheduled_end FROM queue WHERE equipment_id = ? AND scheduled_end IS NOT NULL ORDER BY scheduled_end DESC LIMIT 1"
        );
        $stmt->execute([$equipmentId]);
        $row = $stmt->fetch();
        return $row ? $row['scheduled_end'] : null;
    }

    /**
     * Full calendar data: queue entries with order, sample types, equipment.
     * Excludes finished orders (results_available, completed) so they appear in Order History only.
     * Ordered by queue_type (priority first), then position.
     */
    public function getCalendarData() {
        $stmt = $this->db->prepare(
            "SELECT q.id as queue_id, q.order_id, q.equipment_id, q.position, q.scheduled_start, q.scheduled_end,
                    q.queue_type, o.order_number, o.status as order_status, o.priority, o.estimated_completion,
                    e.name as equipment_name,
                    (SELECT GROUP_CONCAT(DISTINCT s.sample_type ORDER BY s.sample_type) FROM samples s WHERE s.order_id = o.id) as sample_types
             FROM queue q
             JOIN orders o ON q.order_id = o.id
             LEFT JOIN equipment e ON q.equipment_id = e.id
             WHERE o.status NOT IN ('results_available', 'completed')
             ORDER BY q.queue_type DESC, q.position ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Queue Scheduling Methods
    public function scheduleQueueEntry($queueId, $scheduledStart, $scheduledEnd) {
        $stmt = $this->db->prepare(
            "UPDATE queue SET scheduled_start = ?, scheduled_end = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$scheduledStart, $scheduledEnd, $queueId]);
    }

    public function updateSchedule($queueId, $scheduledStart, $scheduledEnd) {
        return $this->scheduleQueueEntry($queueId, $scheduledStart, $scheduledEnd);
    }

    public function recalculateSchedule($queueType, $startingPosition = 1) {
        // Method signature for recalculating schedule for all queue entries
    }

    public function getEstimatedWaitTime($queueId) {
        // Method signature for calculating estimated wait time for a queue entry
    }

    // Queue Processing Methods
    public function startProcessing($queueId) {
        // Method signature for marking queue entry as started
    }

    public function completeProcessing($queueId) {
        // Method signature for marking queue entry as completed
    }

    public function getNextInQueue($queueType) {
        // Method signature for retrieving next order to be processed
    }

    public function isProcessing($queueId) {
        // Method signature for checking if queue entry is being processed
    }

    // Priority Queue Methods
    public function convertToPriority($queueId, $additionalFee) {
        // Method signature for converting standard queue to priority
    }

    public function processPriorityQueue() {
        // Method signature for processing priority queue items
    }

    public function separateQueuesByShift() {
        // Method signature for separating standard and priority by shift times
    }

    // Queue Adjustment Methods
    public function adjustForDelay($equipmentId, $delayDuration) {
        // Method signature for adjusting queue schedule due to equipment delay
    }

    public function redistributeQueue($equipmentId) {
        // Method signature for redistributing queue to other equipment
    }

    public function optimizeQueue($queueType) {
        // Method signature for optimizing queue order based on various factors
    }

    // Queue Statistics Methods
    public function getQueueLength($queueType = null) {
        $sql = "SELECT COUNT(*) as cnt FROM queue q JOIN orders o ON q.order_id = o.id WHERE o.status NOT IN ('results_available', 'completed')";
        if ($queueType) { $sql .= " AND q.queue_type = ?"; $stmt = $this->db->prepare($sql); $stmt->execute([$queueType]); }
        else { $stmt = $this->db->query($sql); }
        return (int) $stmt->fetch()['cnt'];
    }

    public function getAverageWaitTime($queueType = null, $startDate = null, $endDate = null) {
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, o.created_at, q.scheduled_start)) as avg_mins 
                FROM queue q JOIN orders o ON q.order_id = o.id 
                WHERE q.scheduled_start IS NOT NULL AND o.created_at IS NOT NULL";
        $params = [];
        if ($queueType) { $sql .= " AND q.queue_type = ?"; $params[] = $queueType; }
        if ($startDate) { $sql .= " AND q.scheduled_start >= ?"; $params[] = $startDate; }
        if ($endDate) { $sql .= " AND q.scheduled_start <= ?"; $params[] = $endDate; }
        $stmt = $params ? $this->db->prepare($sql) : $this->db->query($sql);
        $params ? $stmt->execute($params) : null;
        $row = $stmt->fetch();
        return $row && $row['avg_mins'] !== null ? round((float) $row['avg_mins'], 1) : 0;
    }

    public function getQueueStatistics($startDate = null, $endDate = null) {
        $standardLen = $this->getQueueLength('standard');
        $priorityLen = $this->getQueueLength('priority');
        $avgWait = $this->getAverageWaitTime(null, $startDate, $endDate);
        return [
            'standard_queue_length' => $standardLen,
            'priority_queue_length' => $priorityLen,
            'total_in_queue' => $standardLen + $priorityLen,
            'average_wait_minutes' => $avgWait,
            'from' => $startDate,
            'to' => $endDate,
        ];
    }

    public function getQueueEntriesForReport($startDate = null, $endDate = null) {
        $sql = "SELECT q.*, o.order_number, o.status as order_status, o.created_at as order_created, e.name as equipment_name 
                FROM queue q 
                JOIN orders o ON q.order_id = o.id 
                LEFT JOIN equipment e ON q.equipment_id = e.id 
                WHERE 1=1";
        $params = [];
        if ($startDate) { $sql .= " AND q.scheduled_start >= ?"; $params[] = $startDate; }
        if ($endDate) { $sql .= " AND q.scheduled_end <= ?"; $params[] = $endDate; }
        $sql .= " ORDER BY q.scheduled_start ASC";
        $stmt = $params ? $this->db->prepare($sql) : $this->db->query($sql);
        if ($params) $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Getters and Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getOrderId() {
        return $this->orderId;
    }

    public function setOrderId($orderId) {
        $this->orderId = $orderId;
    }

    public function getEquipmentId() {
        return $this->equipmentId;
    }

    public function setEquipmentId($equipmentId) {
        $this->equipmentId = $equipmentId;
    }

    public function getPositionValue() {
        return $this->position;
    }

    public function setPositionValue($position) {
        $this->position = $position;
    }

    public function getScheduledStart() {
        return $this->scheduledStart;
    }

    public function setScheduledStart($scheduledStart) {
        $this->scheduledStart = $scheduledStart;
    }

    public function getScheduledEnd() {
        return $this->scheduledEnd;
    }

    public function setScheduledEnd($scheduledEnd) {
        $this->scheduledEnd = $scheduledEnd;
    }

    public function getActualStart() {
        return $this->actualStart;
    }

    public function setActualStart($actualStart) {
        $this->actualStart = $actualStart;
    }

    public function getActualEnd() {
        return $this->actualEnd;
    }

    public function setActualEnd($actualEnd) {
        $this->actualEnd = $actualEnd;
    }

    public function getQueueType() {
        return $this->queueType;
    }

    public function setQueueType($queueType) {
        $this->queueType = $queueType;
    }
}
