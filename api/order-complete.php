<?php
/**
 * POST: Mark an order as completed.
 * Accepts multipart/form-data:
 *  - queue_id (int)
 *  - message (optional text)
 *  - attachment (optional file)
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

$queueId = isset($_POST['queue_id']) ? (int) $_POST['queue_id'] : 0;
$note = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($queueId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid queue_id']);
    exit;
}

// Handle optional attachment
$attachmentPath = null;
$attachmentName = null;
if (!empty($_FILES['attachment']) && isset($_FILES['attachment']['error']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $attachmentPath = $_FILES['attachment']['tmp_name'];
    $attachmentName = $_FILES['attachment']['name'];
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

    // Mark queue entry as completed (actual_end)
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("UPDATE queue SET actual_end = NOW() WHERE id = ?");
    $stmt->execute([$queueId]);

    // Update order status to results_available and set completed_at
    $order->updateOrderStatus($orderId, 'results_available');
    $pdo->prepare("UPDATE orders SET completed_at = NOW() WHERE id = ?")->execute([$orderId]);

    // Notify customer about completion (best-effort)
    $orderData = $order->getOrderWithCustomer($orderId);
    if ($orderData && !empty($orderData['customer_email'])) {
        $email->sendOrderCompletedNotification(
            $orderData['customer_email'],
            $orderData['customer_name'],
            $orderData['order_number'],
            $note,
            $attachmentPath,
            $attachmentName
        );
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

