<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('SampleRepository');

$repo = makeSampleRepository();

/* getById */
$sample = $repo->getById(1);
assertNotNull($sample, 'getById should return sample');
assertSame(1, $sample->getId(), 'sample id should match');
assertSame('Copper', $sample->getCompoundName(), 'compound name should match');
printPass('getById works');

/* createSample */
$new = new Sample();
$new->setOrderId(1);
$new->setOrderTypeId(1);
$new->setUnitCost(50.00);
$new->setSampleType('ore');
$new->setCompoundName('Nickel');
$new->setQuantity(8.0);
$new->setUnit('kg');
$new->setPreparationTime(30);
$new->setTestingTime(null);
$new->setStatus(SampleStatus::PENDING);
$new->setResults(null);

$newId = $repo->createSample($new);
assertTrue($newId > 0, 'createSample should return inserted id');

$created = $repo->getById($newId);
assertNotNull($created, 'created sample should be fetchable');
assertSame('Nickel', $created->getCompoundName(), 'created sample compound should match');
printPass('createSample works');

/* updateSample */
$created->setCompoundName('Nickel Updated');
$created->setQuantity(9.5);
$created->setResults('Updated result');
assertTrue($repo->updateSample($created), 'updateSample should succeed');

$updated = $repo->getById($newId);
assertSame('Nickel Updated', $updated->getCompoundName(), 'updated compound should match');
assertSame(9.5, $updated->getQuantity(), 'updated quantity should match');
assertSame('Updated result', $updated->getResults(), 'updated results should match');
printPass('updateSample works');

/* updateStatus */
assertTrue($repo->updateStatus($newId, SampleStatus::PREPARING), 'updateStatus should succeed');
$statusUpdated = $repo->getById($newId);
assertSame(SampleStatus::PREPARING, $statusUpdated->getStatus(), 'status should update');
printPass('updateStatus works');

/* updateResults */
assertTrue($repo->updateResults($newId, 'Lab complete'), 'updateResults should succeed');
$resultUpdated = $repo->getById($newId);
assertSame('Lab complete', $resultUpdated->getResults(), 'results should update');
printPass('updateResults works');

/* updateTestingTime */
assertTrue($repo->updateTestingTime($newId, 55), 'updateTestingTime should succeed');
$timeUpdated = $repo->getById($newId);
assertSame(55, $timeUpdated->getTestingTime(), 'testing time should update');
printPass('updateTestingTime works');

/* getByOrderId - current first-pass behavior returns raw rows */
$byOrder = $repo->getByOrderId(1);
assertTrue(is_array($byOrder), 'getByOrderId should return array');
assertTrue(count($byOrder) >= 2, 'order 1 should have multiple samples');
assertSame(1, (int)$byOrder[0]['order_id'], 'order_id should match in returned row');
printPass('getByOrderId works');

/* getByStatus - current first-pass behavior returns joined raw rows */
$byStatus = $repo->getByStatus(SampleStatus::PREPARING);
assertTrue(is_array($byStatus), 'getByStatus should return array');
assertCountSame(2, $byStatus, 'preparing should now include seeded row plus updated row');
assertSame('ORD-TEST-001', $byStatus[0]['order_number'], 'joined order number should be present');
printPass('getByStatus works');

/* getStatistics */
$stats = $repo->getStatistics('2026-03-19 00:00:00', '2026-03-20 00:00:00');
assertTrue(is_array($stats), 'getStatistics should return array');
assertTrue(count($stats) >= 1, 'statistics should return grouped rows');
printPass('getStatistics works');

/* deleteById */
assertTrue($repo->deleteById($newId), 'deleteById should succeed');
assertNull($repo->getById($newId), 'deleted sample should no longer exist');
printPass('deleteById works');