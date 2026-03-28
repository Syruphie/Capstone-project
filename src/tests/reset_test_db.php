<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$db = getTestDb();

printSection('Reset Test DB');

$_SESSION = [];

$db->exec('SET FOREIGN_KEY_CHECKS = 0');

$db->exec('TRUNCATE TABLE samples');
$db->exec('TRUNCATE TABLE queue');
$db->exec('TRUNCATE TABLE accounting_sync');
$db->exec('TRUNCATE TABLE notifications');
$db->exec('TRUNCATE TABLE invoices');
$db->exec('TRUNCATE TABLE payment_events');
$db->exec('TRUNCATE TABLE payments');
$db->exec('TRUNCATE TABLE orders');
$db->exec('TRUNCATE TABLE order_types');
$db->exec('TRUNCATE TABLE equipment');
$db->exec('TRUNCATE TABLE users');

$db->exec('SET FOREIGN_KEY_CHECKS = 1');

/* Users */
$stmt = $db->prepare(
    'INSERT INTO users (
        id,
        full_name,
        email,
        password_hash,
        phone,
        company_name,
        address,
        role,
        is_active,
        created_at,
        updated_at,
        last_login
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

$stmt->execute([
    1,
    'Test Customer',
    'customer@test.com',
    password_hash('Password1!', PASSWORD_DEFAULT),
    '4031111111',
    'Customer Co',
    '111 Customer St',
    'customer',
    1,
    '2026-03-19 08:00:00',
    '2026-03-19 08:00:00',
    null,
]);

$stmt->execute([
    2,
    'Test Technician',
    'technician@test.com',
    password_hash('Password1!', PASSWORD_DEFAULT),
    '4032222222',
    'Tech Co',
    '222 Tech St',
    'technician',
    1,
    '2026-03-19 08:15:00',
    '2026-03-19 08:15:00',
    null,
]);

$stmt->execute([
    3,
    'Test Admin',
    'admin@test.com',
    password_hash('Password1!', PASSWORD_DEFAULT),
    '4033333333',
    'Admin Co',
    '333 Admin St',
    'administrator',
    1,
    '2026-03-19 08:30:00',
    '2026-03-19 08:30:00',
    null,
]);

$stmt->execute([
    4,
    'Inactive User',
    'inactive@test.com',
    password_hash('Password1!', PASSWORD_DEFAULT),
    '4034444444',
    'Inactive Co',
    '444 Inactive St',
    'customer',
    0,
    '2026-03-19 08:45:00',
    '2026-03-19 08:45:00',
    null,
]);

/* Orders */
$stmt = $db->prepare(
    'INSERT INTO orders (
        id,
        order_number,
        customer_id,
        status,
        priority,
        total_cost,
        estimated_completion,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);

$stmt->execute([1, 'ORD-TEST-001', 1, 'submitted', 'standard', 0.00, '2026-03-20 10:00:00', '2026-03-19 08:00:00']);
$stmt->execute([2, 'ORD-TEST-002', 1, 'approved', 'priority', 0.00, '2026-03-20 11:00:00', '2026-03-19 08:30:00']);
$stmt->execute([3, 'ORD-TEST-003', 1, 'completed', 'standard', 0.00, '2026-03-20 12:00:00', '2026-03-19 09:00:00']);

/* Order Types */
$stmt = $db->prepare(
    'INSERT INTO order_types (
        id,
        name,
        cost,
        sample_type,
        is_active
    ) VALUES (?, ?, ?, ?, ?)'
);

$stmt->execute([1, 'Ore Analysis', 50.00, 'ore', 1]);
$stmt->execute([2, 'Liquid Analysis', 75.00, 'liquid', 1]);
$stmt->execute([3, 'Inactive Type', 99.00, 'ore', 0]);

/* Equipment */
$stmt = $db->prepare(
    'INSERT INTO equipment (
        id,
        name,
        equipment_type,
        processing_time_per_sample
    ) VALUES (?, ?, ?, ?)'
);

$stmt->execute([1, 'GC-MS', 'Machine', 15]);
$stmt->execute([2, 'HPLC', 'Machine 2', 25]);

/* Samples */
$stmt = $db->prepare(
    'INSERT INTO samples (
        id,
        order_id,
        order_type_id,
        unit_cost,
        sample_type,
        compound_name,
        quantity,
        unit,
        preparation_time,
        testing_time,
        status,
        results,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

$stmt->execute([1, 1, 1, 50.00, 'ore', 'Copper', 10.5, 'kg', 30, null, 'pending', null, '2026-03-19 09:00:00', '2026-03-19 09:00:00']);
$stmt->execute([2, 1, 2, 75.00, 'liquid', 'Acid Mix', 4.0, 'L', 0, null, 'preparing', null, '2026-03-19 09:15:00', '2026-03-19 09:15:00']);
$stmt->execute([3, 2, 1, 50.00, 'ore', 'Iron', 6.0, 'kg', 30, 45, 'testing', 'In progress', '2026-03-19 09:30:00', '2026-03-19 09:30:00']);
$stmt->execute([4, 3, 2, 75.00, 'liquid', 'Water', 2.0, 'L', 0, 20, 'completed', 'Pass', '2026-03-19 09:45:00', '2026-03-19 09:45:00']);

printPass('Test database reset complete');