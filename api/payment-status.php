<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Payment.php';

header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$refresh = isset($_GET['refresh']) ? (bool) intval($_GET['refresh']) : true;
$customerId = (int) $_SESSION['user_id'];

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing order_id']);
    exit;
}

try {
    $payment = new Payment();
    $status = $payment->getPaymentStatusForOrder($orderId, $customerId, $refresh);

    if (!$status) {
        echo json_encode([
            'success' => true,
            'found' => false,
            'message' => 'No payment found for this order yet'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'found' => true,
        'payment' => [
            'id' => (int) $status['id'],
            'order_id' => (int) $status['order_id'],
            'amount' => (float) $status['amount'],
            'currency' => $status['currency'],
            'status' => $status['status'],
            'failure_reason' => $status['failure_reason'],
            'paid_at' => $status['paid_at'],
            'updated_at' => $status['updated_at']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
