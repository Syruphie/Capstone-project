<?php
declare(strict_types=1);

/**
 * Class PaymentReceiptService
 *
 * Handles receipt content generation and payment receipt delivery.
 *
 * Responsibilities:
 * - Build receipt HTML/text content
 * - Email payment receipts to customers
 *
 * Non-Responsibilities:
 * - No payment persistence
 * - No provider API calls
 * - No invoice database writes
 *
 * Design Notes:
 * - Keeps receipt formatting and delivery concerns isolated
 * - Can later integrate with Email domain/services
 */

require_once __DIR__ . '/../../Email/Service/EmailService.php';

class PaymentReceiptService
{
    private PaymentRepository $paymentRepository;
    private InvoiceService $invoiceService;
    private InvoiceRepository $invoiceRepository;

    public function __construct(PaymentRepository $paymentRepository, InvoiceService $invoiceService, InvoiceRepository $invoiceRepository)
    {
        $this->paymentRepository = $paymentRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function emailReceiptForPayment(int $paymentId): bool
    {
        $payment = $this->paymentRepository->getById($paymentId);
        if ($payment === null) {
            return false;
        }

        $invoice = $this->invoiceService->getInvoiceByOrderAndCustomer($payment->getOrderId(), $payment->getCustomerId());
        if (!$invoice) {
            return false;
        }

        $customer = $this->invoiceRepository->getCustomerNameAndEmail($payment->getCustomerId());

        if (!$customer) {
            return false;
        }

        $subject = 'Payment Receipt - ' . APP_NAME . ' - ' . $invoice['invoice_number'];
        $body = $invoice['receipt_html'] . '<p style="font-family:Arial,sans-serif;">You can also view this invoice in your account order history.</p>';

        $emailService = new EmailService();

        return $emailService->send($customer['email'], $subject, $body, true);
    }

    public function buildReceiptHtml(string $invoiceNumber, array $payment, array $orderData): string
    {
        $amount = number_format((float)$payment['amount'], 2);
        $paidAt = !empty($payment['paid_at']) ? date('Y-m-d H:i', strtotime($payment['paid_at'])) : date('Y-m-d H:i');
        $orderTotal = number_format((float)($orderData['total_cost'] ?? $payment['amount']), 2);

        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { border: 1px solid #ddd; border-top: none; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .label { color: #666; width: 40%; }
        .value { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Payment Receipt</h2>
            <p style="margin:6px 0 0 0;">' . APP_NAME . '</p>
        </div>
        <div class="content">
            <p>Thank you, ' . htmlspecialchars($orderData['full_name']) . '. Your payment has been confirmed.</p>
            <table>
                <tr><td class="label">Invoice Number</td><td class="value">' . htmlspecialchars($invoiceNumber) . '</td></tr>
                <tr><td class="label">Order Number</td><td class="value">' . htmlspecialchars($orderData['order_number']) . '</td></tr>
                <tr><td class="label">Transaction ID</td><td class="value">' . htmlspecialchars($payment['provider_payment_intent_id']) . '</td></tr>
                <tr><td class="label">Payment Status</td><td class="value">' . htmlspecialchars(strtoupper($payment['status'])) . '</td></tr>
                <tr><td class="label">Currency</td><td class="value">' . htmlspecialchars($payment['currency']) . '</td></tr>
                <tr><td class="label">Amount Paid</td><td class="value">$' . $amount . '</td></tr>
                <tr><td class="label">Order Total</td><td class="value">$' . $orderTotal . '</td></tr>
                <tr><td class="label">Paid At</td><td class="value">' . htmlspecialchars($paidAt) . '</td></tr>
            </table>
        </div>
    </div>
</body>
</html>';
    }
}