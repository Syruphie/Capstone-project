<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('QueueProcessingService');

$repo = makeQueueRepository();
$queueService = new QueueService($repo);
$processingService = new QueueProcessingService($repo);

$queueId = $queueService->addToQueue(1, 1, QueueType::STANDARD);

assertFalse($processingService->isProcessing($queueId), 'entry should not be processing initially');
printPass('initial processing state is false');

$started = $processingService->startProcessing($queueId);
assertTrue($started, 'startProcessing should succeed');

$entry = $repo->getById($queueId);
assertNotNull($entry->getActualStart(), 'actual_start should be set after startProcessing');
assertTrue($processingService->isProcessing($queueId), 'entry should be processing after start');
printPass('startProcessing works');

$completed = $processingService->completeProcessing($queueId);
assertTrue($completed, 'completeProcessing should succeed');

$entry = $repo->getById($queueId);
assertNotNull($entry->getActualEnd(), 'actual_end should be set after completeProcessing');
assertFalse($processingService->isProcessing($queueId), 'entry should not be processing after completion');
printPass('completeProcessing works');

$queueId2 = $queueService->addToQueue(2, 1, QueueType::STANDARD);
assertFalse($processingService->completeProcessing($queueId2), 'cannot complete before starting');
printPass('complete before start fails');

$next = $processingService->getNextInQueue(QueueType::STANDARD);
assertNotNull($next, 'getNextInQueue should return an entry');
printPass('getNextInQueue works');