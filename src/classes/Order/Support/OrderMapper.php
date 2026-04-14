<?php
declare(strict_types=1);

/**
 * Class OrderMapper
 *
 * Maps database rows to Order entities and vice versa.
 *
 * Responsibilities:
 * - Convert raw database rows into Order entities
 * - Keep row-to-entity translation out of repository logic
 *
 * Non-Responsibilities:
 * - No SQL execution
 * - No workflow/business rules
 */

require_once __DIR__ . '/../Entity/Order.php';

class OrderMapper
{
    public function mapRowToEntity(array $row): Order
    {
        $order = new Order();

        $order->setId(isset($row['id']) ? (int)$row['id'] : null);
        $order->setCustomerId((int)$row['customer_id']);
        $order->setOrderNumber((string)$row['order_number']);
        $order->setStatus((string)$row['status']);
        $order->setPriority((string)$row['priority']);
        $order->setTotalCost(isset($row['total_cost']) ? (float)$row['total_cost'] : 0.0);
        $order->setEstimatedCompletion($row['estimated_completion'] ?? null);
        $order->setApprovedBy(isset($row['approved_by']) ? (int)$row['approved_by'] : null);
        $order->setApprovedAt($row['approved_at'] ?? null);
        $order->setRejectionReason($row['rejection_reason'] ?? null);
        $order->setOrderNote($row['order_note'] ?? null);
        $order->setCreatedAt($row['created_at'] ?? null);
        $order->setUpdatedAt($row['updated_at'] ?? null);
        $order->setCompletedAt($row['completed_at'] ?? null);

        return $order;
    }
}