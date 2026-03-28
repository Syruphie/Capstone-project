<?php
declare(strict_types=1);

require_once __DIR__ . '/../Payment/Repository/PaymentRepository.php';
require_once __DIR__ . '/../Payment/Repository/PaymentEventRepository.php';
require_once __DIR__ . '/../Payment/Repository/InvoiceRepository.php';
require_once __DIR__ . '/../Payment/Repository/NotificationRepository.php';
require_once __DIR__ . '/../Payment/Repository/AccountingSyncRepository.php';
require_once __DIR__ . '/../Payment/Service/PaymentProviderService.php';
require_once __DIR__ . '/../Payment/Service/PaymentStatusService.php';
require_once __DIR__ . '/../Payment/Service/PaymentNotificationService.php';
require_once __DIR__ . '/../Payment/Service/InvoiceService.php';
require_once __DIR__ . '/../Payment/Service/PaymentReceiptService.php';
require_once __DIR__ . '/../Payment/Service/PaymentService.php';
require_once __DIR__ . '/../Payment/Service/PaymentWebhookService.php';
require_once __DIR__ . '/../Order/Repository/OrderRepository.php';

class FrontendPayment
{
    private PaymentService $paymentService;
    private PaymentWebhookService $paymentWebhookService;
    private InvoiceService $invoiceService;

    public function __construct()
    {
        $db = Database::getInstance()->getConnection();

        $paymentRepository = new PaymentRepository($db);
        $paymentEventRepository = new PaymentEventRepository($db);
        $invoiceRepository = new InvoiceRepository($db);
        $notificationRepository = new NotificationRepository($db);
        $accountingSyncRepository = new AccountingSyncRepository($db);
        $paymentProviderService = new PaymentProviderService();
        $paymentStatusService = new PaymentStatusService(new OrderRepository($db));
        $paymentNotificationService = new PaymentNotificationService($notificationRepository);
        $this->invoiceService = new InvoiceService($invoiceRepository, $paymentRepository);
        $paymentReceiptService = new PaymentReceiptService($paymentRepository, $this->invoiceService, $invoiceRepository);

        $this->paymentService = new PaymentService(
            $paymentRepository,
            $paymentProviderService,
            $paymentStatusService,
            $paymentNotificationService,
            $accountingSyncRepository,
            $this->invoiceService,
            $paymentReceiptService
        );

        $this->paymentWebhookService = new PaymentWebhookService(
            $paymentRepository,
            $paymentEventRepository,
            $paymentProviderService,
            $paymentStatusService,
            $paymentNotificationService,
            $accountingSyncRepository,
            $this->invoiceService,
            $paymentReceiptService
        );
    }

    public function createPaymentIntentForOrder(int $orderId, int $customerId, string $email, float $amount, string $currency = 'cad'): \Stripe\PaymentIntent
    {
        return $this->paymentService->createPaymentIntentForOrder($orderId, $customerId, $email, $amount, $currency);
    }

    public function getPaymentStatusForOrder(int $orderId, int $customerId, bool $refreshFromProvider = true): ?array
    {
        return $this->paymentService->getPaymentStatusForOrder($orderId, $customerId, $refreshFromProvider);
    }

    public function handleWebhook(string $payload, ?string $signatureHeader = null): array
    {
        return $this->paymentWebhookService->handleWebhook($payload, $signatureHeader);
    }

    public function getInvoiceByOrderAndCustomer(int $orderId, int $customerId): ?array
    {
        return $this->invoiceService->getInvoiceByOrderAndCustomer($orderId, $customerId);
    }
}

