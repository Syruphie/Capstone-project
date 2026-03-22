<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('OrderApprovalService');

$repo = makeOrderRepository();
$service = new OrderApprovalService($repo);

// Approve valid
$result = $service->approveOrder(1, 2);
assertTrue($result, 'Order approved');
printPass('approveOrder works');

// Reject invalid status
try {
    $service->approveOrder(3, 2); // already approved
    throw new RuntimeException('Expected exception not thrown');
} catch (RuntimeException $e) {
    printPass('cannot approve non-submitted');
}

// Reject missing reason
require __DIR__ . '/../reset_test_db.php';

try {
    $service->rejectOrder(1, '');
    throw new RuntimeException('Expected exception not thrown');
} catch (InvalidArgumentException $e) {
    printPass('reject requires reason');
}