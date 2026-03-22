<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('OrderReportingService');

$repo = makeOrderRepository();
$service = new OrderReportingService($repo);

// Stats
$stats = $service->getOrderStatistics(null, null);
assertTrue(isset($stats['total']), 'stats contain total');
printPass('getOrderStatistics works');

// Revenue
$revenue = $service->getRevenueByPeriod('2026-03-01', '2026-03-30');
assertTrue(isset($revenue['revenue']), 'revenue calculated');
printPass('getRevenueByPeriod works');

// Invalid date
try {
    $service->getRevenueByPeriod('2026-03-30', '2026-03-01');
    throw new RuntimeException('Expected exception not thrown');
} catch (InvalidArgumentException $e) {
    printPass('invalid revenue date rejected');
}