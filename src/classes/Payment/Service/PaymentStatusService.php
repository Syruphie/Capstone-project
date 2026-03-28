<?php
declare(strict_types=1);

/**
 * Class PaymentStatusService
 *
 * Handles payment status interpretation and synchronization with related domains.
 *
 * Responsibilities:
 * - Translate payment/provider statuses into internal outcomes
 * - Synchronize related order status changes based on payment state
 *
 * Non-Responsibilities:
 * - No provider API calls
 * - No raw SQL queries
 * - No notification sending
 *
 * Design Notes:
 * - Keeps payment-to-order state rules centralized
 * - Prevents status mapping logic from being duplicated elsewhere
 */

require_once __DIR__ . '/../../Order/Repository/OrderRepository.php';

class PaymentStatusService
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function syncOrderStatusByPayment(int $orderId, string $paymentStatus): bool
    {
        $targetStatus = null;

        if (in_array($paymentStatus, [PaymentStatus::PENDING, PaymentStatus::REQUIRES_ACTION], true)) {
            $targetStatus = 'payment_pending';
        } elseif ($paymentStatus === PaymentStatus::SUCCEEDED) {
            $targetStatus = 'payment_confirmed';
        } elseif (in_array($paymentStatus, [PaymentStatus::CANCELED, PaymentStatus::FAILED], true)) {
            $targetStatus = 'payment_pending';
        }

        if ($targetStatus === null) {
            return false;
        }

        if ($targetStatus === 'payment_pending') {
            $current = $this->orderRepository->getOrderStatus($orderId);

            if (in_array($current, [
                OrderStatus::PAYMENT_CONFIRMED,
                OrderStatus::IN_QUEUE,
                OrderStatus::PREPARATION_IN_PROGRESS,
                OrderStatus::TESTING_IN_PROGRESS,
                OrderStatus::RESULTS_AVAILABLE,
                OrderStatus::COMPLETED
            ], true)) {
                return true;
            }
        }

        return $this->orderRepository->updateOrderStatus($orderId, $targetStatus);
    }
}