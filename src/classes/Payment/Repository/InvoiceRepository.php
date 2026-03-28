<?php
declare(strict_types=1);

/**
 * Class InvoiceRepository
 *
 * Handles all direct database interactions for invoices.
 *
 * Responsibilities:
 * - Insert and retrieve invoice records
 * - Retrieve invoices by payment, order, or customer
 *
 * Non-Responsibilities:
 * - No invoice number generation
 * - No receipt emailing
 * - No PDF rendering
 *
 * Design Notes:
 * - Acts as the data access layer for the `invoices` table
 * - Uses InvoiceMapper for entity hydration
 */

require_once __DIR__ . '/../Entity/Invoice.php';
require_once __DIR__ . '/../Support/InvoiceMapper.php';

class InvoiceRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getByPaymentId(int $paymentId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE payment_id = ? LIMIT 1");
        $stmt->execute([$paymentId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getLatestByOrderAndCustomer(int $orderId, int $customerId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT i.*, p.status AS payment_status, p.amount, p.currency
             FROM invoices i
             JOIN payments p ON p.id = i.payment_id
             WHERE i.order_id = ? AND i.customer_id = ?
             ORDER BY i.created_at DESC
             LIMIT 1"
        );
        $stmt->execute([$orderId, $customerId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function createInvoice(
        int $paymentId,
        int $orderId,
        int $customerId,
        string $invoiceNumber,
        array $transactionDetails,
        string $receiptHtml
    ): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO invoices (payment_id, order_id, customer_id, invoice_number, transaction_details, receipt_html)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $paymentId,
            $orderId,
            $customerId,
            $invoiceNumber,
            json_encode($transactionDetails),
            $receiptHtml
        ]);
    }

    public function getInvoiceData(int $orderId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.order_number, o.total_cost, u.full_name, u.email, u.company_name
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             WHERE o.id = ?"
        );
        $stmt->execute([$orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getCustomerNameAndEmail(int $customerId): ?array
    {
        $stmt = $this->db->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}