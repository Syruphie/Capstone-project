<?php
declare(strict_types=1);

/**
 * Class PaymentAccountingService
 *
 * Handles creation of downstream accounting synchronization records.
 *
 * Responsibilities:
 * - Create accounting sync entries for successful payments
 * - Support integration between payment workflows and accounting/reporting systems
 *
 * Non-Responsibilities:
 * - No provider API calls
 * - No payment intent creation
 * - No receipt emailing
 *
 * Design Notes:
 * - Keeps accounting-specific side effects outside core payment orchestration
 */
class AccountingSyncRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createAccountingSyncRecord(int $paymentId, int $orderId): bool
    {
        $period = date('Y-m');

        $stmt = $this->db->prepare(
            "INSERT INTO accounting_sync (payment_id, order_id, sync_status, reporting_period, synced_at)
             VALUES (?, ?, 'synced', ?, NOW())"
        );

        return $stmt->execute([$paymentId, $orderId, $period]);
    }
}