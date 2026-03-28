<?php
declare(strict_types=1);

/**
 * Class PaymentRepository
 *
 * Handles all direct database interactions for payments.
 *
 * Responsibilities:
 * - Insert and update payment records
 * - Retrieve payments by ID, order, customer, or provider intent ID
 * - Persist payment status changes and provider identifiers
 *
 * Non-Responsibilities:
 * - No Stripe API calls
 * - No webhook verification
 * - No order status synchronization
 * - No notification or invoice orchestration
 *
 * Design Notes:
 * - Acts as the centralized data access layer for the `payments` table
 * - Uses PaymentMapper for entity hydration
 */

require_once __DIR__ . '/../Entity/Payment.php';
require_once __DIR__ . '/../Support/PaymentMapper.php';

class PaymentRepository
{
    private PDO $db;
    private PaymentMapper $mapper;

    public function __construct(PDO $db, ?PaymentMapper $mapper = null)
    {
        $this->db = $db;
        $this->mapper = $mapper ?? new PaymentMapper();
    }

    public function getById(int $paymentId): ?Payment
    {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ? LIMIT 1");
        $stmt->execute([$paymentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function getByProviderIntentId(string $providerPaymentIntentId): ?Payment
    {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE provider_payment_intent_id = ? LIMIT 1");
        $stmt->execute([$providerPaymentIntentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function getPaymentIdByIntent(string $providerPaymentIntentId): ?int
    {
        if ($providerPaymentIntentId === '') {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id FROM payments WHERE provider_payment_intent_id = ? LIMIT 1");
        $stmt->execute([$providerPaymentIntentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['id'] : null;
    }

    public function getLatestByOrderAndCustomer(int $orderId, int $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM payments
             WHERE order_id = ? AND customer_id = ?
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute([$orderId, $customerId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function upsertFromProviderData(array $intentArr, int $orderId, int $customerId): bool
    {
        if ($orderId <= 0 || $customerId <= 0) {
            return false;
        }

        $providerPayload = json_encode($intentArr);
        $paymentMethodType = null;

        if (!empty($intentArr['payment_method_types']) && is_array($intentArr['payment_method_types'])) {
            $paymentMethodType = $intentArr['payment_method_types'][0] ?? null;
        }

        $status = $intentArr['status'] ?? 'created';
        $failureReason = $intentArr['last_payment_error']['message'] ?? null;
        $amount = ((int)($intentArr['amount'] ?? 0)) / 100;
        $currency = strtoupper($intentArr['currency'] ?? 'cad');
        $paidAt = null;

        if (($intentArr['status'] ?? null) === 'succeeded') {
            $paidAt = date('Y-m-d H:i:s');
        }

        $sql = "INSERT INTO payments
                (order_id, customer_id, provider, provider_payment_intent_id, amount, currency, status, payment_method_type, failure_reason, paid_at, provider_payload)
                VALUES (?, ?, 'stripe', ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    amount = VALUES(amount),
                    currency = VALUES(currency),
                    status = VALUES(status),
                    payment_method_type = VALUES(payment_method_type),
                    failure_reason = VALUES(failure_reason),
                    paid_at = VALUES(paid_at),
                    provider_payload = VALUES(provider_payload),
                    updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $orderId,
            $customerId,
            $intentArr['id'] ?? null,
            $amount,
            $currency,
            $status,
            $paymentMethodType,
            $failureReason,
            $paidAt,
            $providerPayload
        ]);
    }

    public function updateStatus(int $paymentId, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE payments
             SET status = ?, updated_at = CURRENT_TIMESTAMP
             WHERE id = ?"
        );

        return $stmt->execute([$status, $paymentId]);
    }
}