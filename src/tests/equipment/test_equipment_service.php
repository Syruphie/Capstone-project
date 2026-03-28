<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('EquipmentService');

$db = getTestDb();
$equipmentRepository = makeEquipmentRepository();
$delayRepository = makeEquipmentDelayRepository();
$service = new EquipmentService($equipmentRepository, $delayRepository);

$hasEquipmentDelays = (bool)$db->query("SHOW TABLES LIKE 'equipment_delays'")->fetchColumn();

$insertedId = $service->addEquipment('ICP-MS', 'Machine', 20, 10, 3, 5, 12, true, null);
assertTrue($insertedId > 0, 'addEquipment should return inserted id');

$inserted = $service->getEquipmentById($insertedId);
assertNotNull($inserted, 'inserted equipment should be fetchable');
assertSame('ICP-MS', $inserted['name'], 'inserted equipment name should match');
printPass('addEquipment and getEquipmentById work');

$updated = $service->updateEquipment($insertedId, [
    'daily_capacity' => 16,
    'warmup_time' => 12,
]);
assertTrue($updated, 'updateEquipment should persist allowed updates');

$updatedRow = $service->getEquipmentById($insertedId);
assertSame(16, (int)$updatedRow['daily_capacity'], 'daily_capacity should update');
assertSame(12, (int)$updatedRow['warmup_time'], 'warmup_time should update');
printPass('updateEquipment works');

assertTrue($service->setAvailability($insertedId, false), 'setAvailability should update is_available');
$availableRows = $service->getAvailableEquipment();
$availableIds = array_map(static fn(array $row): int => (int)$row['id'], $availableRows);
assertFalse(in_array($insertedId, $availableIds, true), 'unavailable equipment should be filtered out from available list');
printPass('setAvailability and getAvailableEquipment work');

if ($hasEquipmentDelays) {
    assertTrue($service->logDelay($insertedId, '2026-03-20 10:00:00', 45, 'Calibration', 3), 'logDelay should create delay record');
    $delays = $service->getDelayHistory($insertedId);
    assertCountSame(1, $delays, 'delay history should contain logged delay');
    assertSame('Calibration', $delays[0]['reason'], 'delay reason should match');
    printPass('logDelay and getDelayHistory work');

    $stats = $service->getEquipmentStatistics($insertedId);
    assertNotNull($stats, 'getEquipmentStatistics should return aggregate data');
    assertSame(1, $stats['delay_count'], 'delay count should reflect history');
    assertSame($insertedId, (int)$stats['equipment']['id'], 'stats should include requested equipment row');
    printPass('getEquipmentStatistics works');
} else {
    printPass('skipped delay/statistics checks because equipment_delays table is not present in this test schema');
}

$processingTime = $service->calculateProcessingTime($insertedId, 7);
assertSame(162, $processingTime, 'calculateProcessingTime should include base, warmup, and breaks');
printPass('calculateProcessingTime works');

$service->updateEquipment(1, ['daily_capacity' => 10]);
$stmt = $db->prepare(
    'INSERT INTO queue (order_id, equipment_id, position, queue_type, scheduled_start, scheduled_end)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([1, 1, 1, QueueType::STANDARD, '2026-03-20 09:00:00', '2026-03-20 09:30:00']);
$stmt->execute([2, 1, 2, QueueType::STANDARD, '2026-03-20 10:00:00', '2026-03-20 10:30:00']);

$utilization = $service->getUtilizationRate(1, '2026-03-20 00:00:00', '2026-03-21 00:00:00');
assertTrue(abs($utilization - 20.0) < 0.0001, 'utilization rate should match used orders over daily capacity');
printPass('getUtilizationRate works');

if ($hasEquipmentDelays) {
    $allWithStats = $service->getAllEquipmentWithStats();
    assertTrue(count($allWithStats) >= 2, 'getAllEquipmentWithStats should return seeded equipment rows');
    printPass('getAllEquipmentWithStats works');
}

