<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('OrderService');

$repo = makeOrderRepository();
$service = new OrderService($repo);

// Create order
$orderId = $service->createOrder(1);
assertTrue($orderId > 0, 'Order created');
printPass('createOrder works');

// Invalid priority
try {
    $service->createOrder(1, 'invalid');
    throw new RuntimeException('Expected exception not thrown');
} catch (InvalidArgumentException $e) {
    printPass('invalid priority rejected');
}

// Get order
$order = $service->getOrderById(1);
assertNotNull($order, 'Order retrieved');
printPass('getOrderById works');

// Status validation
try {
    $service->updateOrderStatus(1, 'invalid');
    throw new RuntimeException('Expected exception not thrown');
} catch (InvalidArgumentException $e) {
    printPass('invalid status rejected');
}

// Estimated completion validation
try {
    $service->updateEstimatedCompletion(1, '2000-01-01');
    throw new RuntimeException('Expected exception not thrown');
} catch (InvalidArgumentException $e) {
    printPass('past date rejected');
}