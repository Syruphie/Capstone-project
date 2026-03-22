<?php
declare(strict_types=1);

/**
 * Class OrderService
 *
 * Handles core order use cases and orchestration.
 *
 * Responsibilities:
 * - Create new orders
 * - Retrieve core order data
 * - Update general order fields through repository methods
 * - Validate order status before delegating persistence
 *
 * Non-Responsibilities:
 * - No direct SQL
 * - No admin approval/rejection workflows
 * - No reporting/statistics aggregation
 * - No payment processing
 */

require_once __DIR__ . '/../Repository/OrderRepository.php';
require_once __DIR__ . '/../Support/OrderStatus.php';
require_once __DIR__ . '/../Entity/Order.php';
require_once __DIR__ . '/../Support/OrderPriority.php';

class OrderService
{
    private OrderRepository $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createOrder(int $customerId, string $priority = OrderPriority::STANDARD): int
    {
        if (!OrderPriority::isValid($priority)) {
            throw new InvalidArgumentException("Invalid order priority: {$priority}");
        }

        $order = new Order();
        $order->setCustomerId($customerId);
        $order->setOrderNumber($this->generateOrderNumber());
        $order->setStatus(OrderStatus::DRAFT);
        $order->setPriority($priority);
        $order->setTotalCost(0.0);

        return $this->repository->createOrder($order);
    }

    public function getOrderById(int $orderId): ?Order
    {
        return $this->repository->getById($orderId);
    }

    public function getOrderWithCustomer(int $orderId): ?array
    {
        return $this->repository->getByIdWithCustomer($orderId);
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        if (!OrderStatus::isValid($status)) {
            throw new InvalidArgumentException("Invalid order status: {$status}");
        }

        return $this->repository->updateOrderStatus($orderId, $status);
    }

    /**
     * @throws Exception
     */
    public function updateEstimatedCompletion(int $orderId, string $estimatedCompletion): bool
    {
        $newCompletion = new DateTime($estimatedCompletion);
        $now = new DateTime();
        if ($now > $newCompletion) {
            throw new InvalidArgumentException("Estimated completion date must be in the future: {$estimatedCompletion}");
        }
        return $this->repository->updateEstimatedCompletion($estimatedCompletion, $orderId);
    }

    public function updateOrderTotalFromSamples(int $orderId): bool
    {
        return $this->repository->updateTotalFromSamples($orderId);
    }

    public function getOrdersByCustomer(int $customerId, int $limit = 50, int $offset = 0): array
    {
        if ($limit <= 0) {
            $limit = 50;
        }
        return $this->repository->getByCustomer($customerId, $limit, $offset);
    }

    public function getPendingOrders(): array
    {
        return $this->repository->getPending();
    }

    public function getOrdersByStatus(string $status): array
    {
        if (!OrderStatus::isValid($status)) {
            throw new InvalidArgumentException("Invalid order status: {$status}");
        }

        return $this->repository->getByStatus($status);
    }

    public function getAllOrders(int $limit = 50, int $offset = 0): array
    {
        if ($limit <= 0) {
            $limit = 50;
        }
        return $this->repository->getAll($limit, $offset);
    }

    public function searchOrders(string $searchTerm, int $limit = 50): array
    {
        if ($limit <= 0) {
            $limit = 50;
        }
        return $this->repository->searchOrders($searchTerm, $limit);
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}