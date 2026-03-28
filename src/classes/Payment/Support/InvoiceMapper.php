<?php
declare(strict_types=1);

/**
 * Class InvoiceMapper
 *
 * Maps database result rows to Invoice entity objects.
 *
 * Responsibilities:
 * - Convert associative array rows into Invoice entities
 * - Handle basic type casting during hydration
 *
 * Non-Responsibilities:
 * - No database querying
 * - No invoice generation logic
 *
 * Design Notes:
 * - Used by InvoiceRepository for consistent entity hydration
 */

require_once __DIR__ . '/../Entity/Invoice.php';

class InvoiceMapper
{
    public function mapRowToEntity(array $row): Invoice
    {
        $invoice = new Invoice();

        $invoice->setId(isset($row['id']) ? (int)$row['id'] : null);
        $invoice->setPaymentId((int)$row['payment_id']);
        $invoice->setOrderId((int)$row['order_id']);
        $invoice->setCustomerId((int)$row['customer_id']);
        $invoice->setInvoiceNumber((string)$row['invoice_number']);
        $invoice->setAmount((float)$row['amount']);
        $invoice->setCurrency((string)$row['currency']);
        $invoice->setIssuedAt($row['issued_at'] ?? null);
        $invoice->setCreatedAt($row['created_at'] ?? null);
        $invoice->setUpdatedAt($row['updated_at'] ?? null);

        return $invoice;
    }
}