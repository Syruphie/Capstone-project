<?php
require_once __DIR__ . '/../config/database.php';

class Order {
    private $db;
    
    // Properties
    private $id;
    private $customerId;
    private $orderNumber;
    private $status;
    private $priority;
    private $totalCost;
    private $estimatedCompletion;
    private $approvedBy;
    private $approvedAt;
    private $rejectionReason;
    private $createdAt;
    private $updatedAt;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Order Management Methods
    public function createOrder($customerId, $priority = 'standard') {
        $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt = $this->db->prepare(
            "INSERT INTO orders (customer_id, order_number, priority) VALUES (?, ?, ?)"
        );
        $stmt->execute([$customerId, $orderNumber, $priority]);
        
        return $this->db->lastInsertId();
    }

    public function getOrderById($orderId) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function getOrderWithCustomer($orderId) {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.company_name
             FROM orders o
             JOIN users u ON o.customer_id = u.id
             WHERE o.id = ?"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function updateOrderStatus($orderId, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    public function approveOrder($orderId, $approvedBy) {
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$approvedBy, $orderId]);
    }

    public function rejectOrder($orderId, $rejectionReason) {
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = 'rejected', rejection_reason = ? WHERE id = ?"
        );
        return $stmt->execute([$rejectionReason, $orderId]);
    }

    public function calculateTotalCost($orderId) {
        // Method signature for calculating total cost of an order
    }

    public function updateEstimatedCompletion($orderId, $estimatedCompletion) {
        $stmt = $this->db->prepare("UPDATE orders SET estimated_completion = ? WHERE id = ?");
        return $stmt->execute([$estimatedCompletion, $orderId]);
    }

    // Order Retrieval Methods
    public function getOrdersByCustomer($customerId, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                    (SELECT COUNT(*) FROM samples WHERE order_id = o.id) as sample_count
             FROM orders o
             WHERE o.customer_id = ?
             ORDER BY o.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$customerId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getPendingOrders() {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name as customer_name, u.company_name,
                    (SELECT COUNT(*) FROM samples WHERE order_id = o.id) as sample_count
             FROM orders o
             JOIN users u ON o.customer_id = u.id
             WHERE o.status = 'submitted'
             ORDER BY o.created_at ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrdersByStatus($status) {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name as customer_name
             FROM orders o
             JOIN users u ON o.customer_id = u.id
             WHERE o.status = ?
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public function getAllOrders($limit = 50, $offset = 0) {
        // Method signature for retrieving all orders with pagination
    }

    public function searchOrders($searchTerm) {
        // Method signature for searching orders by order number or customer name
    }

    /**
     * Order history: finished orders only (results_available, completed).
     * For customer: only their orders. Optional search by order number or date range.
     */
    public function getOrderHistoryForCustomer($customerId, $searchOrderNumber = '', $searchDateFrom = '', $searchDateTo = '', $limit = 100) {
        $sql = "SELECT o.*, (SELECT COUNT(*) FROM samples WHERE order_id = o.id) as sample_count
                FROM orders o
                WHERE o.customer_id = ? AND o.status IN ('results_available', 'completed')";
        $params = [$customerId];
        if ($searchOrderNumber !== '') {
            $sql .= " AND o.order_number LIKE ?";
            $params[] = '%' . $searchOrderNumber . '%';
        }
        if ($searchDateFrom !== '') {
            $sql .= " AND (o.completed_at >= ? OR o.estimated_completion >= ?)";
            $params[] = $searchDateFrom;
            $params[] = $searchDateFrom;
        }
        if ($searchDateTo !== '') {
            $sql .= " AND (o.completed_at <= ? OR o.estimated_completion <= ?)";
            $params[] = $searchDateTo;
            $params[] = $searchDateTo;
        }
        $sql .= " ORDER BY COALESCE(o.completed_at, o.estimated_completion, o.updated_at) DESC LIMIT ?";
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Order history for admin: all finished orders, optional search by customer name, order number, or date.
     * Returns orders with customer_name for display.
     */
    public function getOrderHistoryForAdmin($searchCustomerName = '', $searchOrderNumber = '', $searchDateFrom = '', $searchDateTo = '', $limit = 200) {
        $sql = "SELECT o.*, u.full_name as customer_name, u.email as customer_email,
                       (SELECT COUNT(*) FROM samples WHERE order_id = o.id) as sample_count
                FROM orders o
                JOIN users u ON o.customer_id = u.id
                WHERE o.status IN ('results_available', 'completed')";
        $params = [];
        if ($searchCustomerName !== '') {
            $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
            $params[] = '%' . $searchCustomerName . '%';
            $params[] = '%' . $searchCustomerName . '%';
        }
        if ($searchOrderNumber !== '') {
            $sql .= " AND o.order_number LIKE ?";
            $params[] = '%' . $searchOrderNumber . '%';
        }
        if ($searchDateFrom !== '') {
            $sql .= " AND COALESCE(o.completed_at, o.estimated_completion, o.updated_at) >= ?";
            $params[] = $searchDateFrom;
        }
        if ($searchDateTo !== '') {
            $sql .= " AND COALESCE(o.completed_at, o.estimated_completion, o.updated_at) <= ?";
            $params[] = $searchDateTo;
        }
        $sql .= " ORDER BY o.priority DESC, COALESCE(o.completed_at, o.estimated_completion, o.updated_at) DESC LIMIT ?";
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Order Timeline Methods
    public function getOrderTimeline($orderId) {
        // Method signature for retrieving order timeline/history
    }

    public function addTimelineEvent($orderId, $event, $details) {
        // Method signature for adding an event to order timeline
    }

    // Payment Methods
    public function processPayment($orderId, $amount, $paymentMethod) {
        // Method signature for processing payment
    }

    public function confirmPayment($orderId) {
        // Method signature for confirming payment received
    }

    public function issueRefund($orderId, $amount, $reason) {
        // Method signature for issuing refund
    }

    // Statistics Methods
    public function getOrderStatistics($startDate = null, $endDate = null) {
        // Method signature for retrieving order statistics
    }

    public function getRevenueByPeriod($startDate, $endDate) {
        // Method signature for calculating revenue in a period
    }

    // Getters and Setters
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getCustomerId() {
        return $this->customerId;
    }

    public function setCustomerId($customerId) {
        $this->customerId = $customerId;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getPriority() {
        return $this->priority;
    }

    public function setPriority($priority) {
        $this->priority = $priority;
    }

    public function getTotalCost() {
        return $this->totalCost;
    }

    public function setTotalCost($totalCost) {
        $this->totalCost = $totalCost;
    }

    public function getEstimatedCompletion() {
        return $this->estimatedCompletion;
    }

    public function setEstimatedCompletion($estimatedCompletion) {
        $this->estimatedCompletion = $estimatedCompletion;
    }

    public function getRejectionReason() {
        return $this->rejectionReason;
    }

    public function setRejectionReason($rejectionReason) {
        $this->rejectionReason = $rejectionReason;
    }
}
