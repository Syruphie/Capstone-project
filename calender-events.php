<?php
session_start();
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();
if (!$user->isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$role = $user->getRole();
$userId = $_SESSION['user_id'] ?? null;
$order = new Order();

if ($role === 'customer') {
    $orders = $order->getOrderHistoryForCustomer($userId);
} else {
    $orders = $order->getOrderHistoryForAdmin();
}

$events = [];
foreach ($orders as $o) {
    if (!empty($o['created_at'])) {
        $events[] = [
            "title" => "Order #" . $o['order_number'] . " Created",
            "date" => $o['created_at'],
            "description" => "Status: " . $o['status'],
            "className" => ($o['priority'] ?? 'standard')
        ];
    }
    if (!empty($o['completed_at'])) {
        $events[] = [
            "title" => "Order #" . $o['order_number'] . " Completed",
            "date" => $o['completed_at'],
            "description" => "Status: " . $o['status'],
            "className" => ($o['priority'] ?? 'standard')
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($events);