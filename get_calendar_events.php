<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

if (!$user->isLoggedIn()) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$order = new Order();

// Use the role and user id from session
$statuses = isset($_GET['status']) ? (array)$_GET['status'] : [];
$searchOrderNumber = $_GET['order_number'] ?? '';
$searchDateFrom = $_GET['date_from'] ?? '';
$searchDateTo = $_GET['date_to'] ?? '';
$events = $order->getCalendarEvents(
    $_SESSION['role'],
    $_SESSION['user_id'],
    $statuses,
    $searchOrderNumber,
    $searchDateFrom,
    $searchDateTo
);

header('Content-Type: application/json');
echo json_encode($events);