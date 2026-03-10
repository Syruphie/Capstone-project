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
$customerId = (int) $_SESSION['user_id'];

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing order_id']);
    exit;
}

try {
    $payment = new Payment();
    $invoice = $payment->getInvoiceByOrderAndCustomer($orderId, $customerId);

    if (!$invoice) {
        echo json_encode(['success' => true, 'found' => false]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'found' => true,
        'invoice' => [
            'invoice_number' => $invoice['invoice_number'],
            'payment_status' => $invoice['payment_status'],
            'amount' => (float) $invoice['amount'],
            'currency' => $invoice['currency'],
            'created_at' => $invoice['created_at']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
