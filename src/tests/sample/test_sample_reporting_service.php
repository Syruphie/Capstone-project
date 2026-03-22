<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('SampleReportingService');

$repo = makeSampleRepository();
$service = new SampleReportingService($repo);

/* getSampleStatistics */
$stats = $service->getSampleStatistics('2026-03-19', '2026-03-20');
assertTrue(is_array($stats), 'getSampleStatistics should return array');
assertTrue(count($stats) >= 1, 'statistics should not be empty');
printPass('getSampleStatistics works');

/* getTotalProcessingTime */
$total = $service->getTotalProcessingTime(3);
assertSame(75, $total, 'processing time should be preparation + testing');
printPass('getTotalProcessingTime works');

/* missing sample */
assertNull($service->getTotalProcessingTime(999999), 'missing sample should return null');
printPass('missing sample handling works');