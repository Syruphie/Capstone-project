<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('QueueSchedulingService');

$repo = makeQueueRepository();
$queueService = new QueueService($repo);
$schedulingService = new QueueSchedulingService($repo);

$queueId = $queueService->addToQueue(1, 1, QueueType::STANDARD);

$scheduled = $schedulingService->scheduleQueueEntry(
    $queueId,
    '2026-03-20 09:00:00',
    '2026-03-20 10:00:00'
);

assertTrue($scheduled, 'scheduleQueueEntry should succeed');

$entry = $repo->getById($queueId);
assertNotNull($entry, 'entry should exist after scheduling');
assertSame('2026-03-20 09:00:00', $entry->getScheduledStart(), 'scheduled start should match');
assertSame('2026-03-20 10:00:00', $entry->getScheduledEnd(), 'scheduled end should match');
printPass('scheduleQueueEntry works');

$wait = $schedulingService->getEstimatedWaitTime($queueId);
assertNotNull($wait, 'getEstimatedWaitTime should return an int when scheduled');
assertTrue($wait >= 0, 'wait time should be zero or positive');
printPass('getEstimatedWaitTime works');

try {
    $schedulingService->scheduleQueueEntry(
        $queueId,
        '2026-03-20 11:00:00',
        '2026-03-20 10:00:00'
    );
    throw new RuntimeException('Expected invalid date range exception was not thrown');
} catch (InvalidArgumentException $e) {
    printPass('invalid schedule date range throws exception');
}