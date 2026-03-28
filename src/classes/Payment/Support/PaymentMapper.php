<?php
declare(strict_types=1);

/**
 * Class PaymentMapper
 *
 * Maps database result rows to Payment entity objects.
 *
 * Responsibilities:
 * - Convert associative array rows into Payment entities
 * - Handle basic type casting during hydration
 *
 * Non-Responsibilities:
 * - No database querying
 * - No business logic
 *
 * Design Notes:
 * - Used by PaymentRepository for consistent entity hydration
 */

require_once __DIR__ . '/../Entity/Payment.php';
require_once __DIR__ . '/PaymentProvider.php';
require_once __DIR__ . '/PaymentStatus.php';

class PaymentMapper
{
    public function mapRowToEntity(array $row): Payment
    {
        $payment = new Payment();

        $payment->setId(isset($row['id']) ? (int)$row['id'] : null);
        $payment->setOrderId((int)$row['order_id']);
        $payment->setCustomerId((int)$row['customer_id']);
        $payment->setAmount((float)$row['amount']);
        $payment->setCurrency((string)$row['currency']);
        $payment->setProvider((string)$row['provider']);
        $payment->setProviderPaymentIntentId($row['provider_payment_intent_id'] ?? null);
        $payment->setStatus((string)$row['status']);
        $payment->setPaidAt($row['paid_at'] ?? null);
        $payment->setCreatedAt($row['created_at'] ?? null);
        $payment->setUpdatedAt($row['updated_at'] ?? null);

        return $payment;
    }
}