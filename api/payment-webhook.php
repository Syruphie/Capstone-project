<?php
require_once __DIR__ . '/../classes/Payment.php';

header('Content-Type: application/json');

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

if (!$payload) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing payload']);
    exit;
}

try {
    $payment = new Payment();
    $result = $payment->handleWebhook($payload, $signature);

    if (!($result['ok'] ?? false)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Webhook rejected']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => $result['message'] ?? 'Processed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
