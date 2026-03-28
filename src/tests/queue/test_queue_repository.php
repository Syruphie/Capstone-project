<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('QueueRepository');

$repo = makeQueueRepository();

$entry = new QueueEntry(
    null,
    1,
    1,
    1,
    QueueType::STANDARD
);

$queueId = $repo->insert($entry);
assertTrue($queueId > 0, 'insert should return a queue id');
printPass('insert returns queue id');

$fetched = $repo->getById($queueId);
assertNotNull($fetched, 'getById should return an entity');
assertSame(1, $fetched->getOrderId(), 'order id should match');
assertSame(1, $fetched->getEquipmentId(), 'equipment id should match');
assertSame(1, $fetched->getPosition(), 'position should match');
assertSame(QueueType::STANDARD, $fetched->getQueueType(), 'queue type should match');
printPass('getById returns mapped QueueEntry');

$nextPosition = $repo->getNextPositionByType(QueueType::STANDARD);
assertSame(2, $nextPosition, 'next standard queue position should be 2');
printPass('getNextPositionByType works');

$repo->insert(new QueueEntry(null, 2, 1, 2, QueueType::STANDARD));
$repo->insert(new QueueEntry(null, 3, 2, 1, QueueType::PRIORITY));

$standardEntries = $repo->getByQueueType(QueueType::STANDARD);
assertSame(2, count($standardEntries), 'should have 2 standard entries');
assertSame(1, $standardEntries[0]->getPosition(), 'first standard entry should be position 1');
assertSame(2, $standardEntries[1]->getPosition(), 'second standard entry should be position 2');
printPass('getByQueueType returns ordered entities');

$countAll = $repo->getActiveEntryCount();
$countStandard = $repo->getActiveEntryCount(QueueType::STANDARD);
$countPriority = $repo->getActiveEntryCount(QueueType::PRIORITY);

assertSame(2, $countAll, 'active entry count should be 2');
assertSame(2, $countStandard, 'standard active entry count should be 2');
assertSame(0, $countPriority, 'priority active entry count should be 0');
printPass('getActiveEntryCount works');

$deleted = $repo->deleteById($queueId);
assertTrue($deleted, 'deleteById should return true');
assertSame(null, $repo->getById($queueId), 'deleted entry should no longer exist');
printPass('deleteById works');