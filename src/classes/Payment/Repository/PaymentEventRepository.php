<?php
declare(strict_types=1);

/**
 * Class PaymentEventRepository
 *
 * Handles persistence for processed payment provider events.
 *
 * Responsibilities:
 * - Check for existing processed provider events
 * - Insert processed event records for idempotency tracking
 *
 * Non-Responsibilities:
 * - No webhook signature verification
 * - No payment update orchestration
 *
 * Design Notes:
 * - Supports safe webhook processing by preventing duplicate handling
 */
class PaymentEventRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function hasProcessedEvent(string $providerEventId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM payment_events WHERE provider_event_id = ? LIMIT 1");
        $stmt->execute([$providerEventId]);

        return (bool)$stmt->fetch();
    }

    public function createProcessedEvent(?int $paymentId, string $provider, string $providerEventId, string $eventType, array $payload): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO payment_events (payment_id, provider, provider_event_id, event_type, payload, processed_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );

        return $stmt->execute([
            $paymentId,
            $provider,
            $providerEventId,
            $eventType,
            json_encode($payload)
        ]);
    }
}