<?php
declare(strict_types=1);

/**
 * Class OrderRepository
 *
 * Handles all direct database interactions for orders.
 *
 * This class is responsible for all persistence and retrieval logic
 * related to the `orders` table and joined order-related display queries.
 *
 * Responsibilities:
 * - Insert and update orders
 * - Retrieve orders by ID, customer, status, or other filters
 * - Execute joined read queries for customer-facing and admin-facing order views
 * - Execute report/statistics data queries
 * - Provide utility persistence operations for order totals and timestamps
 *
 * Non-Responsibilities:
 * - No business workflow logic
 * - No approval/rejection orchestration rules
 * - No payment processing
 * - No timeline/event orchestration
 *
 * Design Notes:
 * - Acts as the centralized data access layer for order operations
 * - May contain both simple CRUD and richer joined queries
 * - Should remain focused on SQL execution and data retrieval/persistence
 */

require_once __DIR__ . '/../Support/OrderMapper.php';
require_once __DIR__ . '/../Entity/Order.php';
require_once __DIR__ . '/../Support/OrderStatus.php';
require_once __DIR__ . '/../Support/ValidateOrderStatus.php';

/**
 *TODO:
 * normalize date parameter handling
 * split projection/query methods
 * revisit priority ordering
 * decide whether update methods should check affected rows
 */
class OrderRepository
{
    private PDO $db;
    private OrderMapper $mapper;

    public function __construct(PDO $db, ?OrderMapper $mapper = null)
    {
        $this->db = $db;
        $this->mapper = $mapper ?? new OrderMapper();
    }

    public function getById(int $orderId): ?Order
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function getByIdWithCustomer(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email, u.company_name
             FROM orders o
             JOIN users u ON o.customer_id = u.id
             WHERE o.id = ?"
        );
        $stmt->execute([$orderId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getByIdForCustomer(int $orderId, int $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                    (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
             FROM orders o
             WHERE o.id = ? AND o.customer_id = ?
             LIMIT 1"
        );
        $stmt->execute([$orderId, $customerId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function createOrder(Order $order): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO orders (
                customer_id,
                order_number,
                status,
                priority,
                total_cost,
                estimated_completion,
                approved_by,
                approved_at,
                rejection_reason
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $order->getCustomerId(),
            $order->getOrderNumber(),
            $order->getStatus(),
            $order->getPriority(),
            $order->getTotalCost(),
            $order->getEstimatedCompletion(),
            $order->getApprovedBy(),
            $order->getApprovedAt(),
            $order->getRejectionReason(),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateOrder(Order $order): bool
    {
        if ($order->getId() === null) {
            throw new InvalidArgumentException('Cannot update order without ID.');
        }

        $stmt = $this->db->prepare(
            "UPDATE orders
             SET customer_id = ?,
                 order_number = ?,
                 status = ?,
                 priority = ?,
                 total_cost = ?,
                 estimated_completion = ?,
                 approved_by = ?,
                 approved_at = ?,
                 rejection_reason = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([
            $order->getCustomerId(),
            $order->getOrderNumber(),
            $order->getStatus(),
            $order->getPriority(),
            $order->getTotalCost(),
            $order->getEstimatedCompletion(),
            $order->getApprovedBy(),
            $order->getApprovedAt(),
            $order->getRejectionReason(),
            $order->getId(),
        ]);
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        ValidateOrderStatus::validate($status);
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET status = ?, updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$status, $orderId]);
    }

    public function cancelByCustomer(int $orderId, int $customerId, string $reason): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET status = ?,
                 rejection_reason = ?,
                 approved_by = NULL,
                 approved_at = NULL,
                 updated_at = NOW()
             WHERE id = ?
               AND customer_id = ?
               AND status IN (?, ?)"
        );

        $stmt->execute([
            OrderStatus::REJECTED,
            $reason,
            $orderId,
            $customerId,
            OrderStatus::SUBMITTED,
            OrderStatus::PENDING_APPROVAL,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function approveOrder(int $approvedBy, int $orderId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET status = ?,
                 approved_by = ?,
                 approved_at = NOW(),
                 rejection_reason = NULL,
                 updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([OrderStatus::APPROVED, $approvedBy, $orderId]);
    }

    public function rejectOrder(string $rejectionReason, int $orderId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET status = ?,
                 rejection_reason = ?,
                 approved_by = NULL,
                 approved_at = NULL,
                 updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([OrderStatus::REJECTED, $rejectionReason, $orderId]);
    }

    /**
     * @throws Exception
     */
    public function updateEstimatedCompletion(string $estimatedCompletion, int $orderId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET estimated_completion = ?, updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$estimatedCompletion, $orderId]);
    }

    public function updateTotalFromSamples(int $orderId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE orders
             SET total_cost = (
                SELECT COALESCE(SUM(unit_cost), 0)
                FROM samples
                WHERE order_id = ?
             ),
             updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$orderId, $orderId]);
    }

    public function getByCustomer(int $customerId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                    (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
             FROM orders o
             WHERE o.customer_id = ?
             ORDER BY o.created_at DESC
             LIMIT " . (int)$limit . " OFFSET " . (int)$offset
        );
        $stmt->execute([$customerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPending(): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name AS customer_name, u.company_name,
                    (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
             FROM orders o
             JOIN users u ON o.customer_id = u.id
             WHERE o.status = ?
             ORDER BY o.created_at"
        );
        $stmt->execute([OrderStatus::SUBMITTED]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatus(string $status): array
    {
        ValidateOrderStatus::validate($status);
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name AS customer_name
             FROM orders o
             JOIN users u ON o.customer_id = u.id
             WHERE o.status = ?
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([$status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email,
                    (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
             FROM orders o
             LEFT JOIN users u ON o.customer_id = u.id
             ORDER BY o.created_at DESC
             LIMIT " . (int)$limit . " OFFSET " . (int)$offset
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchOrders(string $searchTerm, int $limit = 50): array
    {
        $like = '%' . $searchTerm . '%';

        $stmt = $this->db->prepare(
            "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email
             FROM orders o
             LEFT JOIN users u ON o.customer_id = u.id
             WHERE o.order_number LIKE ?
                OR u.full_name LIKE ?
                OR u.email LIKE ?
             ORDER BY o.created_at DESC
             LIMIT " . (int)$limit
        );
        $stmt->execute([$like, $like, $like]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    public function getFinishedHistoryForCustomer(
        int $customerId,
        string $searchOrderNumber = '',
        string $searchDateFrom = '',
        string $searchDateTo = '',
        int $limit = 100
    ): array
    {

        $sql = "SELECT o.*,
                       (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
                FROM orders o
                WHERE o.customer_id = ?
                  AND o.status IN (?, ?)";
        $params = [$customerId, OrderStatus::RESULTS_AVAILABLE, OrderStatus::COMPLETED];

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

        $sql .= " ORDER BY COALESCE(o.completed_at, o.estimated_completion, o.updated_at) DESC
                  LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    public function getFinishedHistoryForAdmin(
        string $searchCustomerName = '',
        string $searchOrderNumber = '',
        string $searchDateFrom = '',
        string $searchDateTo = '',
        int $limit = 200
    ): array
    {

        $sql = "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email,
                       (SELECT COUNT(*) FROM samples WHERE order_id = o.id) AS sample_count
                FROM orders o
                JOIN users u ON o.customer_id = u.id
                WHERE o.status IN (?, ?)";
        $params = [OrderStatus::RESULTS_AVAILABLE, OrderStatus::COMPLETED];

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

        $sql .= " ORDER BY o.priority DESC, COALESCE(o.completed_at, o.estimated_completion, o.updated_at) DESC
                  LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT status, COUNT(*) AS cnt FROM orders WHERE 1=1";
        $params = [];

        if ($startDate !== null) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate;
        }

        $sql .= " GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sql2 = "SELECT COUNT(*) AS total FROM orders WHERE 1=1";
        $params2 = [];

        if ($startDate !== null) {
            $sql2 .= " AND created_at >= ?";
            $params2[] = $startDate;
        }

        if ($endDate !== null) {
            $sql2 .= " AND created_at <= ?";
            $params2[] = $endDate;
        }

        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute($params2);
        $total = (int)$stmt2->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'by_status' => $byStatus,
            'total' => $total,
            'from' => $startDate,
            'to' => $endDate,
        ];
    }

    /**
     * @throws Exception
     */
    public function getRevenueByPeriod(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(total_cost), 0) AS revenue, COUNT(*) AS order_count
             FROM orders
             WHERE status IN (?, ?, ?, ?, ?, ?)
             AND created_at >= ?
             AND created_at <= ?"
        );
        $stmt->execute([OrderStatus::PAYMENT_CONFIRMED, OrderStatus::IN_QUEUE, OrderStatus::PREPARATION_IN_PROGRESS,
            OrderStatus::TESTING_IN_PROGRESS, OrderStatus::RESULTS_AVAILABLE, OrderStatus::COMPLETED, $startDate, $endDate]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['revenue' => 0, 'order_count' => 0];
    }

    /**
     * @throws Exception
     */
    public function getOrdersForReport(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.id, o.order_number, o.status, o.priority, o.total_cost, o.created_at, o.completed_at,
                    u.full_name AS customer_name
             FROM orders o
             LEFT JOIN users u ON o.customer_id = u.id
             WHERE o.created_at >= ? AND o.created_at <= ?
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderStatus(int $orderId): string
    {
        $stmt = $this->db->prepare("SELECT status FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}