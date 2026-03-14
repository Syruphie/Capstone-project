<?php
/**
 * GET: Performance reports. Admin only.
 * Query: type=orders|revenue|equipment|queue, range=day|week|month|year|custom, from=, to= (for custom)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/Equipment.php';
require_once __DIR__ . '/../classes/Queue.php';

header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn() || $user->getRole() !== 'administrator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$range = isset($_GET['range']) ? trim($_GET['range']) : 'month';
$fromParam = isset($_GET['from']) ? trim($_GET['from']) : '';
$toParam = isset($_GET['to']) ? trim($_GET['to']) : '';

function getDateRange($range, $fromParam, $toParam) {
    $now = time();
    if ($range === 'custom' && $fromParam && $toParam) {
        return [date('Y-m-d 00:00:00', strtotime($fromParam)), date('Y-m-d 23:59:59', strtotime($toParam))];
    }
    switch ($range) {
        case 'day':
            return [date('Y-m-d 00:00:00', $now), date('Y-m-d 23:59:59', $now)];
        case 'week':
            return [date('Y-m-d 00:00:00', strtotime('-1 week', $now)), date('Y-m-d 23:59:59', $now)];
        case 'month':
            return [date('Y-m-d 00:00:00', strtotime('-1 month', $now)), date('Y-m-d 23:59:59', $now)];
        case 'year':
            return [date('Y-m-d 00:00:00', strtotime('-1 year', $now)), date('Y-m-d 23:59:59', $now)];
        default:
            return [date('Y-m-d 00:00:00', strtotime('-1 month', $now)), date('Y-m-d 23:59:59', $now)];
    }
}

list($from, $to) = getDateRange($range, $fromParam, $toParam);

try {
    $order = new Order();
    $equipment = new Equipment();
    $queue = new Queue();

    switch ($type) {
        case 'orders':
            $stats = $order->getOrderStatistics($from, $to);
            $rows = $order->getOrdersForReport($from, $to);
            echo json_encode(['success' => true, 'report' => 'orders', 'from' => $from, 'to' => $to, 'statistics' => $stats, 'rows' => $rows]);
            break;
        case 'revenue':
            $rev = $order->getRevenueByPeriod($from, $to);
            $rows = $order->getOrdersForReport($from, $to);
            echo json_encode(['success' => true, 'report' => 'revenue', 'from' => $from, 'to' => $to, 'revenue' => (float) ($rev['revenue'] ?? 0), 'order_count' => (int) ($rev['order_count'] ?? 0), 'rows' => $rows]);
            break;
        case 'equipment':
            $data = $equipment->getAllEquipmentWithStats();
            echo json_encode(['success' => true, 'report' => 'equipment', 'from' => $from, 'to' => $to, 'equipment' => $data]);
            break;
        case 'queue':
            $stats = $queue->getQueueStatistics($from, $to);
            $rows = $queue->getQueueEntriesForReport($from, $to);
            echo json_encode(['success' => true, 'report' => 'queue', 'from' => $from, 'to' => $to, 'statistics' => $stats, 'rows' => $rows]);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid report type']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
