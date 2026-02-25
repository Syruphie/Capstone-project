<?php
/**
 * GET: Calendar queue data + equipment utilization.
 * Auth: admin or technician.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Queue.php';
require_once __DIR__ . '/../classes/Equipment.php';

header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
$role = $user->getRole();
if (!in_array($role, ['administrator', 'technician'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

try {
    $queue = new Queue();
    $equipment = new Equipment();

    $queueEntries = $queue->getCalendarData();
    $equipmentList = $equipment->getAllEquipment();

    $from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d 00:00:00');
    $to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d 23:59:59', strtotime('+7 days'));

    $utilization = [];
    foreach ($equipmentList as $eq) {
        $slots = $queue->getQueueByEquipment((int) $eq['id'], $from, $to);
        $utilization[] = [
            'id' => (int) $eq['id'],
            'name' => $eq['name'],
            'equipment_type' => $eq['equipment_type'],
            'slots' => array_map(function ($s) {
                return [
                    'queue_id' => (int) $s['id'],
                    'order_id' => (int) $s['order_id'],
                    'order_number' => $s['order_number'],
                    'scheduled_start' => $s['scheduled_start'],
                    'scheduled_end' => $s['scheduled_end'],
                    'order_status' => $s['order_status'],
                ];
            }, $slots),
        ];
    }

    $data = [
        'queue' => array_map(function ($r) {
            return [
                'queue_id' => (int) $r['queue_id'],
                'order_id' => (int) $r['order_id'],
                'order_number' => $r['order_number'],
                'order_status' => $r['order_status'],
                'priority' => $r['priority'],
                'sample_types' => $r['sample_types'] ? explode(',', $r['sample_types']) : [],
                'equipment_id' => $r['equipment_id'] ? (int) $r['equipment_id'] : null,
                'equipment_name' => $r['equipment_name'] ?? null,
                'scheduled_start' => $r['scheduled_start'],
                'scheduled_end' => $r['scheduled_end'],
                'estimated_completion' => $r['estimated_completion'],
                'position' => (int) $r['position'],
                'queue_type' => $r['queue_type'],
            ];
        }, $queueEntries),
        'equipment' => $equipmentList,
        'utilization' => $utilization,
    ];

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
