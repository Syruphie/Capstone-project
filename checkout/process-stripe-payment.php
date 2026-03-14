<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Order.php';
require_once __DIR__ . '/../classes/Payment.php';
header('Content-Type: application/json');

$user = new User();
if (!$user->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$orderId = isset($input['order_id']) ? (int) $input['order_id'] : 0;
$customerId = (int) $_SESSION['user_id'];

if (!$email || $orderId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$order = new Order();
$orderData = $order->getOrderById($orderId);
if (!$orderData || (int) $orderData['customer_id'] !== $customerId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid order']);
    exit;
}

$amount = (float) ($orderData['total_cost'] ?? 0);
if ($amount <= 0) {
    $amount = 20.00;
}

try {
    $payment = new Payment();
    $paymentIntent = $payment->createPaymentIntentForOrder($orderId, $customerId, $email, $amount, 'cad');

    echo json_encode([
        'success' => true,
        'client_secret' => $paymentIntent->client_secret,
        'payment_intent_id' => $paymentIntent->id
    ]);
} catch (\Stripe\Exception\CardException $e) {
    echo json_encode(['success' => false, 'error' => $e->getError()->message]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
