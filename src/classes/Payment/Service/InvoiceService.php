<?php
declare(strict_types=1);

/**
 * Class InvoiceService
 *
 * Handles invoice generation and retrieval workflows.
 *
 * Responsibilities:
 * - Generate invoices for eligible successful payments
 * - Build invoice numbers and invoice entity data
 * - Retrieve invoices by order/payment/customer context
 *
 * Non-Responsibilities:
 * - No direct provider API calls
 * - No raw SQL queries outside delegated lookup helpers
 * - No receipt emailing
 *
 * Design Notes:
 * - Coordinates payment and invoice repositories
 * - Keeps invoice-specific orchestration separate from core payment processing
 */

require_once __DIR__ . '/../Repository/InvoiceRepository.php';
require_once __DIR__ . '/../Repository/PaymentRepository.php';

class InvoiceService
{
    private InvoiceRepository $invoiceRepository;
    private PaymentRepository $paymentRepository;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        PaymentRepository $paymentRepository
    )
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function generateInvoiceForPayment(int $paymentId, PaymentReceiptService $receiptService): bool
    {
        $existing = $this->invoiceRepository->getByPaymentId($paymentId);
        if ($existing) {
            return true;
        }

        $payment = $this->paymentRepository->getById($paymentId);
        if ($payment === null || $payment->getStatus() !== 'succeeded') {
            return false;
        }

        $orderData = $this->invoiceRepository->getInvoiceData($payment->getOrderId());

        if (!$orderData) {
            return false;
        }

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad((string)$paymentId, 6, '0', STR_PAD_LEFT);

        $transactionDetails = [
            'payment_intent_id' => $payment->getProviderPaymentIntentId(),
            'amount' => $payment->getAmount(),
            'currency' => $payment->getCurrency(),
            'status' => $payment->getStatus(),
            'paid_at' => $payment->getPaidAt(),
            'order_number' => $orderData['order_number'],
            'customer_email' => $orderData['email']
        ];

        $paymentData = [
            'provider_payment_intent_id' => $payment->getProviderPaymentIntentId(),
            'amount' => $payment->getAmount(),
            'currency' => $payment->getCurrency(),
            'status' => $payment->getStatus(),
            'paid_at' => $payment->getPaidAt()
        ];

        $receiptHtml = $receiptService->buildReceiptHtml($invoiceNumber, $paymentData, $orderData);

        return $this->invoiceRepository->createInvoice(
            $paymentId,
            $payment->getOrderId(),
            $payment->getCustomerId(),
            $invoiceNumber,
            $transactionDetails,
            $receiptHtml
        );
    }

    public function getInvoiceByOrderAndCustomer(int $orderId, int $customerId): ?array
    {
        return $this->invoiceRepository->getLatestByOrderAndCustomer($orderId, $customerId);
    }
}