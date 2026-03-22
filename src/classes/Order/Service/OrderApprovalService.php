<?php
declare(strict_types=1);

/**
 * Class OrderApprovalService
 *
 * Handles order approval and rejection workflows.
 *
 * Responsibilities:
 * - Approve submitted orders
 * - Reject submitted orders
 * - Enforce simple workflow guards around approval actions
 *
 * Non-Responsibilities:
 * - No SQL outside repository
 * - No reporting/statistics
 * - No payment processing
 * - No order history queries
 */

require_once __DIR__ . '/../Repository/OrderRepository.php';
require_once __DIR__ . '/../Support/OrderStatus.php';

class OrderApprovalService
{
    private OrderRepository $repository;

    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function approveOrder(int $orderId, int $approvedBy): bool
    {
        $order = $this->repository->getById($orderId);

        if ($order === null) {
            throw new RuntimeException("Order {$orderId} not found.");
        }

        if ($order->getStatus() !== OrderStatus::SUBMITTED) {
            throw new RuntimeException("Only submitted orders can be approved.");
        }

        return $this->repository->approveOrder($approvedBy, $orderId);
    }

    public function rejectOrder(int $orderId, string $rejectionReason): bool
    {
        $order = $this->repository->getById($orderId);

        if ($order === null) {
            throw new RuntimeException("Order {$orderId} not found.");
        }

        if ($order->getStatus() !== OrderStatus::SUBMITTED) {
            throw new RuntimeException("Only submitted orders can be rejected.");
        }

        if (trim($rejectionReason) === '') {
            throw new InvalidArgumentException('Rejection reason is required.');
        }

        return $this->repository->rejectOrder($rejectionReason, $orderId);
    }
}