<?php
declare(strict_types=1);

use Stripe\Exception\ApiErrorException;

/**
 * Class PaymentService
 *
 * Core orchestration service for payment lifecycle operations.
 *
 * Responsibilities:
 * - Coordinate payment intent creation
 * - Coordinate payment status retrieval and synchronization
 * - Delegate provider, repository, and supporting service operations
 *
 * Non-Responsibilities:
 * - No raw SQL queries
 * - No direct Stripe SDK interaction
 * - No webhook signature verification
 *
 * Design Notes:
 * - Serves as the primary entry point for payment workflows
 * - Coordinates repositories and specialized payment services
 */
class PaymentService
{
    private PaymentRepository $paymentRepository;
    private PaymentProviderService $paymentProviderService;
    private PaymentStatusService $paymentStatusService;
    private PaymentNotificationService $paymentNotificationService;
    private AccountingSyncRepository $accountingSyncRepository;
    private InvoiceService $invoiceService;
    private PaymentReceiptService $paymentReceiptService;

    public function __construct(
        PaymentRepository $paymentRepository,
        PaymentProviderService $paymentProviderService,
        PaymentStatusService $paymentStatusService,
        PaymentNotificationService $paymentNotificationService,
        AccountingSyncRepository $accountingSyncRepository,
        InvoiceService $invoiceService,
        PaymentReceiptService $paymentReceiptService
    )
    {
        $this->paymentRepository = $paymentRepository;
        $this->paymentProviderService = $paymentProviderService;
        $this->paymentStatusService = $paymentStatusService;
        $this->paymentNotificationService = $paymentNotificationService;
        $this->accountingSyncRepository = $accountingSyncRepository;
        $this->invoiceService = $invoiceService;
        $this->paymentReceiptService = $paymentReceiptService;
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentIntentForOrder(
        int $orderId,
        int $customerId,
        string $email,
        float $amount,
        string $currency = 'cad'
    ): \Stripe\PaymentIntent
    {
        $paymentIntent = $this->paymentProviderService->createPaymentIntent(
            $orderId,
            $customerId,
            $email,
            $amount,
            $currency
        );

        $intentArr = method_exists($paymentIntent, 'toArray') ? $paymentIntent->toArray() : (array)$paymentIntent;

        $ok = $this->paymentRepository->upsertFromProviderData($intentArr, $orderId, $customerId);

        if ($ok) {
            $status = $intentArr['status'] ?? 'created';
            $paymentId = $this->paymentRepository->getPaymentIdByIntent($intentArr['id'] ?? '');

            $this->paymentStatusService->syncOrderStatusByPayment($orderId, $status);
            $this->paymentNotificationService->createNotificationsForPaymentState(
                $paymentId,
                $orderId,
                $customerId,
                $status,
                $amount,
                $intentArr['last_payment_error']['message'] ?? null
            );

            if ($status === 'succeeded' && $paymentId !== null) {
                $this->accountingSyncRepository->createAccountingSyncRecord($paymentId, $orderId);
                $this->invoiceService->generateInvoiceForPayment($paymentId, $this->paymentReceiptService);
                $this->paymentReceiptService->emailReceiptForPayment($paymentId);
            }
        }

        return $paymentIntent;
    }

    /**
     * @throws ApiErrorException
     */
    public function getPaymentStatusForOrder(int $orderId, int $customerId, bool $refreshFromProvider = true): ?array
    {
        $payment = $this->paymentRepository->getLatestByOrderAndCustomer($orderId, $customerId);
        if (!$payment) {
            return null;
        }

        if ($refreshFromProvider && !empty($payment['provider_payment_intent_id'])) {
            $intent = $this->paymentProviderService->retrievePaymentIntent($payment['provider_payment_intent_id']);
            $intentArr = method_exists($intent, 'toArray') ? $intent->toArray() : (array)$intent;

            $this->paymentRepository->upsertFromProviderData($intentArr, $orderId, $customerId);
            $status = $intentArr['status'] ?? 'created';
            $this->paymentStatusService->syncOrderStatusByPayment($orderId, $status);
            $payment = $this->paymentRepository->getLatestByOrderAndCustomer($orderId, $customerId);
        }

        if ($payment) {
            $this->ensureInvoiceForSucceededPayment($payment);
        }

        return $payment;
    }

    private function ensureInvoiceForSucceededPayment(array $payment): void
    {
        $paymentId = isset($payment['id']) ? (int)$payment['id'] : 0;
        $status = (string)($payment['status'] ?? '');

        if ($paymentId <= 0 || $status !== 'succeeded') {
            return;
        }

        $this->invoiceService->generateInvoiceForPayment($paymentId, $this->paymentReceiptService);
    }
}