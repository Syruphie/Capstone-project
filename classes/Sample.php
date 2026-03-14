<?php
require_once __DIR__ . '/../config/database.php';

class Sample {
    private $db;
    
    // Properties
    private $id;
    private $orderId;
    private $sampleType;
    private $compoundName;
    private $quantity;
    private $unit;
    private $preparationTime;
    private $testingTime;
    private $status;
    private $results;
    private $createdAt;
    private $updatedAt;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Sample Management Methods – sample_type comes from the order type (catalogue)
    public function addSample($orderId, $orderTypeId, $compoundName, $quantity, $unit) {
        $orderTypeId = (int) $orderTypeId;
        $stmt = $this->db->prepare("SELECT cost, sample_type FROM order_types WHERE id = ? AND is_active = 1");
        $stmt->execute([$orderTypeId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        $cost = (float) $row['cost'];
        $sampleType = in_array($row['sample_type'], ['ore', 'liquid'], true) ? $row['sample_type'] : 'ore';
        $preparationTime = ($sampleType === 'ore') ? 30 : 0;
        $stmt = $this->db->prepare(
            "INSERT INTO samples (order_id, order_type_id, unit_cost, sample_type, compound_name, quantity, unit, preparation_time) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$orderId, $orderTypeId, $cost, $sampleType, $compoundName, $quantity, $unit, $preparationTime]);
        $sampleId = $this->db->lastInsertId();
        if ($sampleId) {
            require_once __DIR__ . '/Order.php';
            $order = new Order();
            $order->updateOrderTotalFromSamples($orderId);
        }
        return $sampleId;
    }

    public function getSampleById($sampleId) {
        $stmt = $this->db->prepare("SELECT * FROM samples WHERE id = ?");
        $stmt->execute([$sampleId]);
        return $stmt->fetch();
    }

    public function getSamplesByOrder($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM samples WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function updateSampleStatus($sampleId, $status) {
        $allowed = ['pending', 'preparing', 'ready', 'testing', 'completed'];
        if (!in_array($status, $allowed, true)) return false;
        $stmt = $this->db->prepare("UPDATE samples SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, (int) $sampleId]);
    }

    public function deleteSample($sampleId) {
        // Method signature for deleting a sample
    }

    // Sample Preparation Methods
    public function startPreparation($sampleId) {
        // Method signature for starting sample preparation
    }

    public function completePreparation($sampleId) {
        // Method signature for completing sample preparation
    }

    public function calculatePreparationTime($sampleType) {
        // Method signature for calculating preparation time based on sample type
    }

    // Sample Testing Methods
    public function startTesting($sampleId) {
        // Method signature for starting sample testing
    }

    public function completeTesting($sampleId, $results) {
        // Method signature for completing sample testing and recording results
    }

    public function calculateTestingTime($sampleId, $equipmentId) {
        // Method signature for calculating testing time based on equipment
    }

    public function updateResults($sampleId, $results) {
        // Method signature for updating test results
    }

    // Sample Tracking Methods
    public function getSamplesByStatus($status) {
        $stmt = $this->db->prepare(
            "SELECT s.*, o.order_number FROM samples s JOIN orders o ON s.order_id = o.id WHERE s.status = ? ORDER BY s.created_at ASC"
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public function getPendingSamples() {
        return $this->getSamplesByStatus('pending');
    }

    public function getSamplesInPreparation() {
        // Method signature for retrieving samples currently in preparation
    }

    public function getSamplesInTesting() {
        // Method signature for retrieving samples currently being tested
    }

    // Sample Analytics Methods
    public function getTotalProcessingTime($sampleId) {
        // Method signature for calculating total processing time
    }

    public function getSampleStatistics($startDate = null, $endDate = null) {
        // Method signature for retrieving sample statistics
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

    public function getSampleType() {
        return $this->sampleType;
    }

    public function setSampleType($sampleType) {
        $this->sampleType = $sampleType;
    }

    public function getCompoundName() {
        return $this->compoundName;
    }

    public function setCompoundName($compoundName) {
        $this->compoundName = $compoundName;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
    }

    public function getUnit() {
        return $this->unit;
    }

    public function setUnit($unit) {
        $this->unit = $unit;
    }

    public function getPreparationTime() {
        return $this->preparationTime;
    }

    public function setPreparationTime($preparationTime) {
        $this->preparationTime = $preparationTime;
    }

    public function getTestingTime() {
        return $this->testingTime;
    }

    public function setTestingTime($testingTime) {
        $this->testingTime = $testingTime;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getResults() {
        return $this->results;
    }

    public function setResults($results) {
        $this->results = $results;
    }
}
