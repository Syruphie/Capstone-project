<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('SampleTrackingService');

$repo = makeSampleRepository();
$service = new SampleTrackingService($repo);

$pending = $service->getPendingSamples();
assertTrue(is_array($pending), 'getPendingSamples should return array');
assertCountSame(1, $pending, 'there should be one pending sample initially');
printPass('getPendingSamples works');

$preparing = $service->getSamplesInPreparation();
assertTrue(is_array($preparing), 'getSamplesInPreparation should return array');
assertCountSame(1, $preparing, 'there should be one preparing sample initially');
printPass('getSamplesInPreparation works');

$testing = $service->getSamplesInTesting();
assertTrue(is_array($testing), 'getSamplesInTesting should return array');
assertCountSame(1, $testing, 'there should be one testing sample initially');
printPass('getSamplesInTesting works');

$byStatus = $service->getSamplesByStatus(SampleStatus::COMPLETED);
assertTrue(is_array($byStatus), 'getSamplesByStatus should return array');
assertCountSame(1, $byStatus, 'there should be one completed sample initially');
assertSame('ORD-TEST-003', $byStatus[0]['order_number'], 'joined order number should be present');
printPass('getSamplesByStatus works');