<?php
require_once __DIR__ . '/../config/database.php';

class Order
{
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

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Order Management Methods
    public function createOrder($customerId, $priority = 'standard')
    {
        $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare(
            "INSERT INTO orders (customer_id, order_number, priority) VALUES (?, ?, ?)"
        );
        $stmt->execute([$customerId, $orderNumber, $priority]);

        return $this->db->lastInsertId();
    }

    public function getOrderById($orderId)
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function getOrderWithCustomer($orderId)
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.company_name
             FROM orders o
             JOIN users u ON o.customer_id = u.id
             WHERE o.id = ?"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function updateOrderStatus($orderId, $status)
    {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    public function approveOrder($orderId, $approvedBy)
    {
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$approvedBy, $orderId]);
    }

    public function rejectOrder($orderId, $rejectionReason)
    {
        $stmt = $this->db->prepare(
            "UPDATE orders SET status = 'rejected', rejection_reason = ? WHERE id = ?"
        );
        return $stmt->execute([$rejectionReason, $orderId]);
    }

    public function getOrderHistoryByCustomer($customerId, $limit = 200, $offset = 0)
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                (SELECT COUNT(*) FROM samples WHERE order_id = o.id) as sample_count
         FROM orders o
         WHERE o.customer_id = ?
           AND o.status IN ('approved', 'processing', 'completed', 'rejected')
         ORDER BY o.created_at DESC
         LIMIT ? OFFSET ?"
        );
        $stmt->execute([$customerId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    public function getOrderHistoryForTechnician()
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                u.full_name AS customer_name,
                u.company_name,
                (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         WHERE o.status IN ('approved', 'processing', 'completed', 'rejected')
         ORDER BY o.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllOrders()
    {
        $query = "
    SELECT 
        o.*, 
        u.full_name AS customer_name
    FROM orders o
    LEFT JOIN users u 
        ON o.customer_id = u.id
    ORDER BY o.created_at DESC
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getOrderHistoryForAdmin()
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                u.full_name AS customer_name,
                u.company_name,
                (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         WHERE o.status IN ('approved', 'processing', 'completed', 'rejected')
         ORDER BY o.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderByIdForCustomer($orderId, $customerId)
    {
        $stmt = $this->db->prepare(
            "SELECT *
         FROM orders
         WHERE id = ? AND customer_id = ?
         LIMIT 1"
        );
        $stmt->execute([$orderId, $customerId]);
        return $stmt->fetch();
    }

    public function getSamplesByOrderId($orderId)
    {
        $stmt = $this->db->prepare(
            "SELECT *
         FROM samples
         WHERE order_id = ?
         ORDER BY id DESC"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function calculateTotalCost($orderId)
    {
        // Method signature for calculating total cost of an order
    }

    public function updateEstimatedCompletion($orderId, $estimatedCompletion)
    {
        // Method signature for updating estimated completion time
    }

    // Order Retrieval Methods
    public function getOrdersByCustomer($customerId, $limit = 50, $offset = 0)
    {
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

    public function getPendingOrders()
    {
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

    public function getOrdersByStatus($status)
    {
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

    public function getApprovedOrdersForTechnician(){
    $stmt = $this->db->prepare(
        "SELECT o.*,
                u.full_name AS customer_name,
                u.company_name,
                (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         WHERE o.status = 'approved'
           AND o.priority IN ('standard', 'priority')
         ORDER BY o.created_at DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
    }



    public function searchOrders($searchTerm)
    {
        // Method signature for searching orders by order number or customer name
    }

    // Order Timeline Methods
    public function getOrderTimeline($orderId)
    {
        // Method signature for retrieving order timeline/history
    }

    public function addTimelineEvent($orderId, $event, $details)
    {
        // Method signature for adding an event to order timeline
    }

    // Payment Methods
    public function processPayment($orderId, $amount, $paymentMethod)
    {
        // Method signature for processing payment
    }

    public function confirmPayment($orderId)
    {
        // Method signature for confirming payment received
    }

    public function issueRefund($orderId, $amount, $reason)
    {
        // Method signature for issuing refund
    }

    // Statistics Methods
    public function getOrderStatistics($startDate = null, $endDate = null)
    {
        // Method signature for retrieving order statistics
    }

    public function getRevenueByPeriod($startDate, $endDate)
    {
        // Method signature for calculating revenue in a period
    }

    // Getters and Setters
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getTotalCost()
    {
        return $this->totalCost;
    }

    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }

    public function getEstimatedCompletion()
    {
        return $this->estimatedCompletion;
    }

    public function setEstimatedCompletion($estimatedCompletion)
    {
        $this->estimatedCompletion = $estimatedCompletion;
    }

    public function getRejectionReason()
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason($rejectionReason)
    {
        $this->rejectionReason = $rejectionReason;
    }
}
