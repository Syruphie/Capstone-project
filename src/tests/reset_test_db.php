<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$db = getTestDb();

printSection('Reset Test DB');

$db->exec("SET FOREIGN_KEY_CHECKS = 0");

$db->exec("TRUNCATE TABLE queue");
$db->exec("TRUNCATE TABLE orders");
$db->exec("TRUNCATE TABLE equipment");

$db->exec("SET FOREIGN_KEY_CHECKS = 1");

$stmt = $db->prepare(
    "INSERT INTO orders (
        id,
        order_number,
        customer_id,
        status,
        priority,
        estimated_completion,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?)"
);

$stmt->execute([1, 'ORD-TEST-001', 101, 'submitted', 0, '2026-03-20 10:00:00', '2026-03-19 08:00:00']);
$stmt->execute([2, 'ORD-TEST-002', 102, 'pending_approval', 1, '2026-03-20 11:00:00', '2026-03-19 08:30:00']);
$stmt->execute([3, 'ORD-TEST-003', 103, 'approved', 0, '2026-03-20 12:00:00', '2026-03-19 09:00:00']);

$stmt = $db->prepare(
    "INSERT INTO equipment (
        id,
        name,
        equipment_type,
        processing_time_per_sample
    ) VALUES (?, ?, ?, ?)"
);

$stmt->execute([1, 'GC-MS', 'Machine', 15]);
$stmt->execute([2, 'HPLC', 'Machine 2', 25]);

printPass('Test database reset complete');