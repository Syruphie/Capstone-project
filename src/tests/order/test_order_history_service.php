<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('OrderHistoryService');

$repo = makeOrderRepository();
$service = new OrderHistoryService($repo);

// Basic fetch
$results = $service->getOrderHistoryForCustomer(101, null, null, null);
assertTrue(is_array($results), 'returns array');
printPass('basic history fetch works');

// Invalid range
try {
    $service->getOrderHistoryForCustomer(101, null, '2026-03-20', '2026-03-01');
    throw new RuntimeException('Expected exception not thrown');
} catch (InvalidArgumentException $e) {
    printPass('invalid date range rejected');
}