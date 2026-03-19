<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('QueueService');

$repo = makeQueueRepository();
$service = new QueueService($repo);

$queueId = $service->addToQueue(1, 1, QueueType::STANDARD);
assertTrue($queueId > 0, 'addToQueue should return id');

$entry = $service->findById($queueId);
assertNotNull($entry, 'findById should return inserted entry');
assertSame(1, $entry->getPosition(), 'first inserted standard entry should have position 1');
assertSame(QueueType::STANDARD, $entry->getQueueType(), 'queue type should be standard');
printPass('addToQueue works');

$queueId2 = $service->addScheduledToQueue(
    2,
    1,
    QueueType::STANDARD,
    '2026-03-20 09:00:00',
    '2026-03-20 10:00:00'
);

$entry2 = $service->findById($queueId2);
assertNotNull($entry2, 'scheduled entry should exist');
assertSame(2, $entry2->getPosition(), 'second standard entry should have position 2');
assertSame('2026-03-20 09:00:00', $entry2->getScheduledStart(), 'scheduled start should match');
assertSame('2026-03-20 10:00:00', $entry2->getScheduledEnd(), 'scheduled end should match');
printPass('addScheduledToQueue works');

$removed = $service->removeFromQueue($queueId);
assertTrue($removed, 'removeFromQueue should return true');
assertSame(null, $service->findById($queueId), 'removed entry should not exist');
printPass('removeFromQueue works');

try {
    $service->addToQueue(3, 2, 'not-real');
    throw new RuntimeException('Expected exception was not thrown');
} catch (InvalidArgumentException $e) {
    printPass('invalid queue type throws exception');
}