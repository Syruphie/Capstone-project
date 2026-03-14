<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Payment.php';

$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$customerId = (int) $_SESSION['user_id'];

if ($orderId <= 0) {
    http_response_code(400);
    echo 'Missing order_id';
    exit;
}

$payment = new Payment();
$invoice = $payment->getInvoiceByOrderAndCustomer($orderId, $customerId);

if (!$invoice) {
    http_response_code(404);
    echo 'Invoice not found.';
    exit;
}

echo $invoice['receipt_html'];
