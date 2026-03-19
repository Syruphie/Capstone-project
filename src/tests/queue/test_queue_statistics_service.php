<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('QueueStatisticsService');

$repo = makeQueueRepository();
$queueService = new QueueService($repo);
$statsService = new QueueStatisticsService($repo);

$queueService->addScheduledToQueue(
    1,
    1,
    QueueType::STANDARD,
    '2026-03-19 09:00:00',
    '2026-03-19 10:00:00'
);

$queueService->addScheduledToQueue(
    2,
    1,
    QueueType::PRIORITY,
    '2026-03-19 09:30:00',
    '2026-03-19 10:30:00'
);

$summary = $statsService->getQueueStatistics('2026-03-19 00:00:00', '2026-03-20 00:00:00');

assertSame(1, $summary['standard_queue_length'], 'standard queue length should be 1');
assertSame(1, $summary['priority_queue_length'], 'priority queue length should be 1');
assertSame(2, $summary['total_in_queue'], 'total in queue should be 2');
assertTrue(is_float($summary['average_wait_minutes']), 'average wait should be a float');
printPass('getQueueStatistics works');