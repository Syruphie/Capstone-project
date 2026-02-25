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

    /**
     * Retrieve orders for calendar/history depending on role and optional filters.
     * Filters may include order number, date range (created/estimated/completed), and statuses.
     */
    public function getOrdersForRole($role, $userId = null, $searchOrderNumber = '', $searchDateFrom = '', $searchDateTo = '', $filterStatuses = []) {
        $sql = "SELECT o.*, u.full_name as customer_name, u.email as customer_email
                FROM orders o
                LEFT JOIN users u ON o.customer_id = u.id
                WHERE 1=1";
        $params = [];

        if ($role === 'customer') {
            $sql .= " AND o.customer_id = ?";
            $params[] = $userId;
        }

        if ($searchOrderNumber !== '') {
            $sql .= " AND o.order_number LIKE ?";
            $params[] = '%' . $searchOrderNumber . '%';
        }
        if ($searchDateFrom !== '') {
            $sql .= " AND (o.created_at >= ? OR o.estimated_completion >= ? OR o.completed_at >= ?)";
            $params[] = $searchDateFrom;
            $params[] = $searchDateFrom;
            $params[] = $searchDateFrom;
        }
        if ($searchDateTo !== '') {
            $sql .= " AND (o.created_at <= ? OR o.estimated_completion <= ? OR o.completed_at <= ?)";
            $params[] = $searchDateTo;
            $params[] = $searchDateTo;
            $params[] = $searchDateTo;
        }
        if (!empty($filterStatuses)) {
            $placeholders = implode(',', array_fill(0, count($filterStatuses), '?'));
            $sql .= " AND o.status IN ($placeholders)";
            foreach ($filterStatuses as $s) {
                $params[] = $s;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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

    /**
 * Fetch orders as events for calendar display.
 * Customer sees only their orders, admin sees all.
 */
public function getCalendarEvents($role, $userId = null, $filterStatuses = [], $searchOrderNumber = '', $searchDateFrom = '', $searchDateTo = '') {
    $events = [];

    // delegate retrieval to helper which handles filters
    $orders = $this->getOrdersForRole($role, $userId, $searchOrderNumber, $searchDateFrom, $searchDateTo, $filterStatuses);

    // helper color map
    $getColor = function($status) {
        switch ($status) {
            case 'submitted':
            case 'pending_approval': return '#ffc107'; // yellow
            case 'approved':
            case 'payment_pending':
            case 'payment_confirmed': return '#0d6efd'; // blue
            case 'in_queue':
            case 'preparation_in_progress':
            case 'testing_in_progress': return '#fd7e14'; // orange
            case 'results_available':
            case 'completed': return '#198754'; // green
            case 'rejected': return '#dc3545'; // red
            default: return '#6c757d'; // grey
        }
    };

    foreach ($orders as $o) {
        // Event for when order was created/submitted
        if (!empty($o['created_at'])) {
            $events[] = [
                'title' => "Order #" . ($o['order_number'] ?? $o['id']) . " Submitted",
                'date' => $o['created_at'],
                'description' => "Status: " . ($o['status'] ?? 'N/A'),
                'status' => $o['status'] ?? null,
                'color' => $getColor($o['status'] ?? '')
            ];
        }
        // Event for approval action
        if (!empty($o['approved_at'])) {
            $events[] = [
                'title' => "Order #" . ($o['order_number'] ?? $o['id']) . " Approved",
                'date' => $o['approved_at'],
                'description' => "Status: approved",
                'status' => 'approved',
                'color' => $getColor('approved')
            ];
        }
        // Event for rejection
        if (($o['status'] ?? '') === 'rejected') {
            $date = !empty($o['updated_at']) ? $o['updated_at'] : $o['created_at'];
            $events[] = [
                'title' => "Order #" . ($o['order_number'] ?? $o['id']) . " Rejected",
                'date' => $date,
                'description' => "Status: rejected",
                'status' => 'rejected',
                'color' => $getColor('rejected')
            ];
        }

        // Event for estimated completion
        if (!empty($o['estimated_completion'])) {
            $events[] = [
                'title' => "Order #" . ($o['order_number'] ?? $o['id']) . " Estimated Completion",
                'date' => $o['estimated_completion'],
                'description' => "Status: " . ($o['status'] ?? 'N/A'),
                'status' => $o['status'] ?? null,
                'color' => $getColor($o['status'] ?? '')
            ];
        }

        // Event for completed orders
        if (!empty($o['completed_at'])) {
            $events[] = [
                'title' => "Order #" . ($o['order_number'] ?? $o['id']) . " Completed",
                'date' => $o['completed_at'],
                'description' => "Status: " . ($o['status'] ?? 'N/A'),
                'status' => $o['status'] ?? null,
                'color' => $getColor($o['status'] ?? '')
            ];
        }

        // Event for delivery date (optional)
        if (!empty($o['delivery_date'])) {
            $events[] = [
                'title' => "Order #" . ($o['order_number'] ?? $o['id']) . " Delivery",
                'date' => $o['delivery_date'],
                'description' => "Status: " . ($o['status'] ?? 'N/A'),
                'status' => $o['status'] ?? null,
                'color' => $getColor($o['status'] ?? '')
            ];
        }
    }

    return $events;
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
