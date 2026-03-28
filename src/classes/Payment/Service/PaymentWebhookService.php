<?php
declare(strict_types=1);

/**
 * Class PaymentWebhookService
 *
 * Handles orchestration of inbound payment provider webhook events.
 *
 * Responsibilities:
 * - Validate and process payment webhook events
 * - Prevent duplicate event processing
 * - Update payment records from provider data
 * - Trigger downstream payment-related workflows
 *
 * Non-Responsibilities:
 * - No direct SQL queries outside delegated repositories
 * - No direct provider SDK setup
 *
 * Design Notes:
 * - Coordinates provider, repository, and supporting services
 * - Centralizes webhook-specific workflow handling
 */

require_once __DIR__ . '/../Repository/PaymentRepository.php';
require_once __DIR__ . '/../Repository/PaymentEventRepository.php';

class PaymentWebhookService
{
    private PaymentRepository $paymentRepository;
    private PaymentEventRepository $paymentEventRepository;
    private PaymentProviderService $paymentProviderService;
    private PaymentStatusService $paymentStatusService;
    private PaymentNotificationService $paymentNotificationService;
    private AccountingSyncRepository $accountingSyncRepository;
    private InvoiceService $invoiceService;
    private PaymentReceiptService $paymentReceiptService;

    public function __construct(
        PaymentRepository $paymentRepository,
        PaymentEventRepository $paymentEventRepository,
        PaymentProviderService $paymentProviderService,
        PaymentStatusService $paymentStatusService,
        PaymentNotificationService $paymentNotificationService,
        AccountingSyncRepository $accountingSyncRepository,
        InvoiceService $invoiceService,
        PaymentReceiptService $paymentReceiptService
    )
    {
        $this->paymentRepository = $paymentRepository;
        $this->paymentEventRepository = $paymentEventRepository;
        $this->paymentProviderService = $paymentProviderService;
        $this->paymentStatusService = $paymentStatusService;
        $this->paymentNotificationService = $paymentNotificationService;
        $this->accountingSyncRepository = $accountingSyncRepository;
        $this->invoiceService = $invoiceService;
        $this->paymentReceiptService = $paymentReceiptService;
    }

    public function handleWebhook(string $payload, ?string $signatureHeader = null): array
    {
        try {
            $event = $this->paymentProviderService->constructWebhookEvent($payload, $signatureHeader);
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $eventArr = is_array($event)
            ? $event
            : (method_exists($event, 'toArray') ? $event->toArray() : json_decode(json_encode($event), true));

        $eventId = $eventArr['id'] ?? null;
        $eventType = $eventArr['type'] ?? '';
        $intent = $eventArr['data']['object'] ?? null;

        if (!$eventId || !$intent) {
            return ['ok' => false, 'error' => 'Invalid event payload'];
        }

        if ($this->paymentEventRepository->hasProcessedEvent($eventId)) {
            return ['ok' => true, 'message' => 'Event already processed'];
        }

        $intentId = $intent['id'] ?? '';
        $orderId = (int)($intent['metadata']['order_id'] ?? 0);
        $customerId = (int)($intent['metadata']['customer_id'] ?? 0);

        if (($orderId <= 0 || $customerId <= 0) && $intentId !== '') {
            $existingPayment = $this->paymentRepository->getByProviderIntentId($intentId);
            if ($existingPayment !== null) {
                $orderId = $existingPayment->getOrderId();
                $customerId = $existingPayment->getCustomerId();
            }
        }

        if ($orderId > 0 && $customerId > 0) {
            $ok = $this->paymentRepository->upsertFromProviderData($intent, $orderId, $customerId);

            if ($ok) {
                $paymentId = $this->paymentRepository->getPaymentIdByIntent($intentId);
                $status = $intent['status'] ?? 'created';
                $amount = ((int)($intent['amount'] ?? 0)) / 100;
                $failureReason = $intent['last_payment_error']['message'] ?? null;

                $this->paymentStatusService->syncOrderStatusByPayment($orderId, $status);
                $this->paymentNotificationService->createNotificationsForPaymentState(
                    $paymentId,
                    $orderId,
                    $customerId,
                    $status,
                    $amount,
                    $failureReason
                );

                if ($status === 'succeeded' && $paymentId !== null) {
                    $this->accountingSyncRepository->createAccountingSyncRecord($paymentId, $orderId);
                    $this->invoiceService->generateInvoiceForPayment($paymentId, $this->paymentReceiptService);
                    $this->paymentReceiptService->emailReceiptForPayment($paymentId);
                }
            }
        }

        $paymentId = $this->paymentRepository->getPaymentIdByIntent($intentId);

        $this->paymentEventRepository->createProcessedEvent(
            $paymentId,
            'stripe',
            $eventId,
            $eventType,
            $eventArr
        );

        return ['ok' => true, 'message' => 'Processed'];
    }
}