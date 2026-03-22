<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('SampleService');

$db = getTestDb();
$sampleRepo = makeSampleRepository();
$orderRepo = makeOrderRepository();
$service = new SampleService($sampleRepo, $orderRepo, new SampleMapper());

/* addSample */
$newId = $service->addSample(1, 1, 'Zinc', 12.0, 'kg');
assertTrue($newId > 0, 'addSample should return inserted id');

$created = $sampleRepo->getById($newId);
assertNotNull($created, 'created sample should exist');
assertSame('Zinc', $created->getCompoundName(), 'compound should match');
assertSame('ore', $created->getSampleType(), 'sample type should come from order type');
assertSame(30, $created->getPreparationTime(), 'ore preparation time should be 30');
assertSame(SampleStatus::PENDING, $created->getStatus(), 'new sample status should be pending');
printPass('addSample works');

/* order total should update */
$order = $orderRepo->getById(1);
assertNotNull($order, 'order should still exist');
assertSame(175.0, $order->getTotalCost(), 'order total should reflect all samples for order 1');
printPass('order total recalculation works');

/* getSampleById */
$fetched = $service->getSampleById($newId);
assertNotNull($fetched, 'getSampleById should return sample');
assertSame($newId, $fetched->getId(), 'returned sample id should match');
printPass('getSampleById works');

/* getSamplesByOrder */
$byOrder = $service->getSamplesByOrder(1);
assertTrue(is_array($byOrder), 'getSamplesByOrder should return array');
assertTrue(count($byOrder) >= 3, 'order 1 should now have at least 3 samples');
printPass('getSamplesByOrder works');

/* updateSampleStatus */
assertTrue($service->updateSampleStatus($newId, SampleStatus::PREPARING), 'updateSampleStatus should succeed');
assertSame(SampleStatus::PREPARING, $sampleRepo->getById($newId)->getStatus(), 'status should update');
printPass('updateSampleStatus works');

/* calculatePreparationTime */
assertSame(30, $service->calculatePreparationTime('ore'), 'ore prep time should be 30');
assertSame(0, $service->calculatePreparationTime('liquid'), 'liquid prep time should be 0');
printPass('calculatePreparationTime works');

/* deleteSample */
assertTrue($service->deleteSample($newId), 'deleteSample should succeed');
assertNull($sampleRepo->getById($newId), 'deleted sample should no longer exist');

$orderAfterDelete = $orderRepo->getById(1);
assertSame(125.0, $orderAfterDelete->getTotalCost(), 'order total should recalculate after delete');
printPass('deleteSample works');

/* invalid quantity */
try {
    $service->addSample(1, 1, 'Bad Sample', 0.0, 'kg');
    throw new RuntimeException('Expected invalid quantity exception was not thrown');
} catch (InvalidArgumentException $e) {
    printPass('invalid quantity handling works');
}