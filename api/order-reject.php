<?php
/**
 * POST: Cancel an already-scheduled analysis from the calendar / queue.
 * This marks the order as rejected and notifies the customer with the provided reason.
 * Accepts JSON:
 *  - queue_id (int)
 *  - order_id (int)
 *  - rejection_reason (string)
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
$orderId = isset($input['order_id']) ? (int) $input['order_id'] : 0;
$reason = isset($input['rejection_reason']) ? trim($input['rejection_reason']) : 'Order rejected';

if ($queueId < 1 || $orderId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid queue_id or order_id']);
    exit;
}

try {
    $queue = new Queue();
    $order = new Order();
    $email = new Email();

    $entry = $queue->getQueueById($queueId);
    if (!$entry || (int) $entry['order_id'] !== $orderId) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Queue entry not found for order']);
        exit;
    }

    $orderData = $order->getOrderWithCustomer($orderId);

    if (!$order->rejectOrder($orderId, $reason)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to reject order']);
        exit;
    }

    $queue->removeFromQueue($queueId);

    if ($orderData && !empty($orderData['customer_email'])) {
        $email->sendOrderRejectionNotification(
            $orderData['customer_email'],
            $orderData['customer_name'],
            $orderData['order_number'],
            $reason
        );
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

