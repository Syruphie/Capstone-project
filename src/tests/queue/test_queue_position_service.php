<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('QueuePositionService');

$repo = makeQueueRepository();
$queueService = new QueueService($repo);
$positionService = new QueuePositionService($repo);

$id1 = $queueService->addToQueue(1, 1, QueueType::STANDARD);
$id2 = $queueService->addToQueue(2, 1, QueueType::STANDARD);
$id3 = $queueService->addToQueue(3, 1, QueueType::STANDARD);

assertTrue($positionService->moveUp($id3), 'moveUp should succeed');
$entries = $repo->getByQueueType(QueueType::STANDARD);

assertSame($id1, $entries[0]->getId(), 'first entry should remain first');
assertSame($id3, $entries[1]->getId(), 'third entry should move to second');
assertSame($id2, $entries[2]->getId(), 'second entry should move to third');
printPass('moveUp works');

assertTrue($positionService->moveDown($id1), 'moveDown should succeed');
$entries = $repo->getByQueueType(QueueType::STANDARD);

assertSame($id3, $entries[0]->getId(), 'id3 should now be first');
assertSame($id1, $entries[1]->getId(), 'id1 should now be second');
assertSame($id2, $entries[2]->getId(), 'id2 should remain third');
printPass('moveDown works');

assertTrue($positionService->updatePosition($id2, 1), 'updatePosition should succeed');
$entries = $repo->getByQueueType(QueueType::STANDARD);

assertSame($id2, $entries[0]->getId(), 'id2 should now be first');
printPass('updatePosition works');

$currentPosition = $positionService->getPosition($id2);
assertSame(1, $currentPosition, 'getPosition should return current position');
printPass('getPosition works');

assertFalse($positionService->moveUp(999999), 'moveUp on missing id should fail');
printPass('invalid id handling works');