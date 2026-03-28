<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('PaymentStatusService');

$orderRepository = makeOrderRepository();
$service = new PaymentStatusService($orderRepository);

$ok = $service->syncOrderStatusByPayment(1, PaymentStatus::SUCCEEDED);
assertTrue($ok, 'succeeded payment status should sync order status');

$order = $orderRepository->getById(1);
assertNotNull($order, 'seeded order should exist');
assertSame(OrderStatus::PAYMENT_CONFIRMED, $order->getStatus(), 'order status should become payment_confirmed');
printPass('syncOrderStatusByPayment maps succeeded to payment_confirmed');

$unchanged = $service->syncOrderStatusByPayment(1, PaymentStatus::REFUNDED);
assertFalse($unchanged, 'unsupported payment status should not sync order status');

$orderAfterUnsupported = $orderRepository->getById(1);
assertSame(OrderStatus::PAYMENT_CONFIRMED, $orderAfterUnsupported->getStatus(), 'unsupported status should leave order unchanged');
printPass('syncOrderStatusByPayment ignores unsupported statuses');

