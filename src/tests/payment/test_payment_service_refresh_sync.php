<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('PaymentService refresh sync');

class FakePaymentProviderServiceForRefresh extends PaymentProviderService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function createPaymentIntent(
        int $orderId,
        int $customerId,
        string $email,
        float $amount,
        string $currency = 'cad'
    ): Stripe\PaymentIntent {
        return Stripe\PaymentIntent::constructFrom([
            'id' => 'pi_refresh_sync_001',
            'amount' => (int)round($amount * 100),
            'currency' => $currency,
            'status' => 'requires_payment_method',
            'metadata' => [
                'order_id' => (string)$orderId,
                'customer_id' => (string)$customerId,
            ],
        ]);
    }

    public function retrievePaymentIntent(string $providerPaymentIntentId): Stripe\PaymentIntent
    {
        return Stripe\PaymentIntent::constructFrom([
            'id' => $providerPaymentIntentId,
            'amount' => 5000,
            'currency' => 'cad',
            'status' => 'succeeded',
            'metadata' => [
                'order_id' => '1',
                'customer_id' => '1',
            ],
        ]);
    }
}

$db = getTestDb();
$paymentRepository = makePaymentRepository();
$orderRepository = makeOrderRepository();
$invoiceRepository = makeInvoiceRepository();

$paymentRepository->upsertFromProviderData([
    'id' => 'pi_refresh_sync_001',
    'amount' => 5000,
    'currency' => 'cad',
    'status' => 'requires_payment_method',
    'payment_method_types' => ['card'],
], 1, 1);

$orderRepository->updateOrderStatus(1, OrderStatus::PAYMENT_PENDING);

$paymentStatusService = new PaymentStatusService($orderRepository);
$notificationService = new PaymentNotificationService(makeNotificationRepository());
$accountingSyncRepository = makeAccountingSyncRepository();
$invoiceService = new InvoiceService($invoiceRepository, $paymentRepository);
$receiptService = new PaymentReceiptService($paymentRepository, $invoiceService, $invoiceRepository);

$service = new PaymentService(
    $paymentRepository,
    new FakePaymentProviderServiceForRefresh(),
    $paymentStatusService,
    $notificationService,
    $accountingSyncRepository,
    $invoiceService,
    $receiptService
);

$status = $service->getPaymentStatusForOrder(1, 1, true);
assertNotNull($status, 'payment status should be returned after refresh');
assertSame('succeeded', $status['status'], 'refreshed status should be succeeded');

$order = $orderRepository->getById(1);
assertNotNull($order, 'order should exist after sync');
assertSame(OrderStatus::PAYMENT_CONFIRMED, $order->getStatus(), 'refresh path should sync order status to payment_confirmed');
printPass('refresh sync updates order status to payment_confirmed');

$invoice = $invoiceRepository->getLatestByOrderAndCustomer(1, 1);
assertNotNull($invoice, 'invoice should be generated for succeeded payment during refresh sync');
assertSame('succeeded', $invoice['payment_status'], 'invoice should be tied to succeeded payment');
printPass('refresh sync backfills invoice for succeeded payment');

