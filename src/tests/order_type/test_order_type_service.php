<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('OrderTypeService');

$repository = makeOrderTypeRepository();
$service = new OrderTypeService($repository);

$all = $service->getAll(false);
$active = $service->getAll(true);

assertCountSame(3, $all, 'seeded order types should include 3 records');
assertCountSame(2, $active, 'activeOnly should return two records from seed data');
printPass('getAll supports active filter');

$createdId = $service->create('Rock Density', 'Density profile test', 'invalid_type', 110.50);
assertTrue($createdId > 0, 'create should return inserted order type id');

$created = $service->getById($createdId);
assertNotNull($created, 'created order type should be fetchable');
assertSame('ore', $created['sample_type'], 'invalid sample type should normalize to ore');
assertSame('Rock Density', $created['name'], 'created name should match');
printPass('create normalizes sample type and persists data');

$updated = $service->update($createdId, [
    'sample_type' => 'liquid',
    'cost' => 135.25,
    'is_active' => false,
]);
assertTrue($updated, 'update should succeed with allowed fields');

$updatedRow = $service->getById($createdId);
assertSame('liquid', $updatedRow['sample_type'], 'sample_type should update to liquid');
assertTrue(abs((float)$updatedRow['cost'] - 135.25) < 0.0001, 'cost should update');
assertSame(0, (int)$updatedRow['is_active'], 'is_active should update to false');
printPass('update persists normalized fields');

$noOp = $service->update($createdId, ['unknown_column' => 'ignored']);
assertFalse($noOp, 'update should return false when no valid columns are provided');
printPass('update rejects no-op payloads');

$deleted = $service->delete($createdId);
assertTrue($deleted, 'delete should remove order type');
assertNull($service->getById($createdId), 'deleted order type should not exist');
printPass('delete removes order type');

