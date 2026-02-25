<?php
/**
 * POST: Reschedule a queue entry. Body: { "queue_id": 1, "scheduled_start": "...", "scheduled_end": "..." }
 * Auth: admin or technician.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Queue.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/Email.php';

header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
if (!in_array($user->getRole(), ['administrator', 'technician'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$queueId = isset($input['queue_id']) ? (int) $input['queue_id'] : 0;
$start = isset($input['scheduled_start']) ? trim($input['scheduled_start']) : '';
$end = isset($input['scheduled_end']) ? trim($input['scheduled_end']) : '';
$note = isset($input['message']) ? trim($input['message']) : '';

if ($queueId < 1 || $start === '' || $end === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing queue_id, scheduled_start, or scheduled_end']);
    exit;
}

$startTs = strtotime($start);
$endTs = strtotime($end);
if ($startTs === false || $endTs === false || $endTs <= $startTs) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid dates']);
    exit;
}

try {
    $queue = new Queue();
    $order = new Order();
    $email = new Email();
    $entry = $queue->getQueueById($queueId);
    if (!$entry) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Queue entry not found']);
        exit;
    }
    $orderId = (int) $entry['order_id'];

    $newStartStr = date('Y-m-d H:i:s', $startTs);
    $newEndStr = date('Y-m-d H:i:s', $endTs);

    $queue->updateSchedule($queueId, $newStartStr, $newEndStr);
    $order->updateEstimatedCompletion($orderId, $newEndStr);

    // Notify customer about schedule change (best-effort)
    $orderData = $order->getOrderWithCustomer($orderId);
    if ($orderData && !empty($orderData['customer_email'])) {
        $email->sendOrderScheduleUpdate(
            $orderData['customer_email'],
            $orderData['customer_name'],
            $orderData['order_number'],
            date('Y-m-d H:i', $startTs),
            date('Y-m-d H:i', $endTs),
            $note
        );
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
