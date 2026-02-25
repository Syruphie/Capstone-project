<?php
// redirect to unified history page with calendar
header('Location: order-history.php');
exit;

require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: login.php'); exit;
}

$role = $user->getRole();
$userId = $_SESSION['user_id'] ?? null;
$order = new Order();

$searchOrderNumber = $_GET['order_number'] ?? '';
$searchDateFrom = $_GET['date_from'] ?? '';
$searchDateTo = $_GET['date_to'] ?? '';
$searchCustomerName = $_GET['customer_name'] ?? '';

if ($role === 'customer') {
    $orders = $order->getOrderHistoryForCustomer($userId, $searchOrderNumber, $searchDateFrom, $searchDateTo);
} else {
    $orders = $order->getOrderHistoryForAdmin($searchCustomerName, $searchOrderNumber, $searchDateFrom, $searchDateTo);
}

$standardOrders = array_filter($orders, fn($o) => ($o['priority'] ?? '') === 'standard');
$prioritizedOrders = array_filter($orders, fn($o) => ($o['priority'] ?? '') === 'prioritized');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History & Calendar - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <style>
    /* calendar + table styling */
    #calendar {
        max-width:900px;
        margin:20px auto;
        background:#fff;
        padding:15px;
        border-radius:6px;
        box-shadow:0 2px 8px rgba(0,0,0,.1);
        min-height:400px;
    }
    .history-section{margin:30px auto;max-width:900px;}
    .admin-table{width:100%;border-collapse:collapse;margin-top:10px;}
    .admin-table th,.admin-table td{padding:8px 12px;border:1px solid #ddd;text-align:left;}
    .admin-table th{background:#f5f5f5;}
    .badge{display:inline-block;padding:2px 6px;border-radius:4px;font-size:12px;color:#fff;}
    .badge-priority{background:#e67e22;}
    .badge-standard{background:#3498db;}
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <h1>Order History & Calendar</h1>
    <form method="get" class="search-form" style="max-width:900px;margin:0 auto 20px;">
        <input type="text" name="order_number" placeholder="Order #" value="<?= htmlspecialchars($searchOrderNumber) ?>" />
        <input type="date" name="date_from" value="<?= htmlspecialchars($searchDateFrom) ?>" />
        <input type="date" name="date_to" value="<?= htmlspecialchars($searchDateTo) ?>" />
        <?php if($role !== 'customer'): ?>
            <input type="text" name="customer_name" placeholder="Customer name/email" value="<?= htmlspecialchars($searchCustomerName) ?>" />
        <?php endif; ?>
        <button type="submit">Search</button>
    </form>
    <div id="calendar"></div>

    <!-- tables as before… -->

</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,timeGridDay' },
        events: 'get_calendar_events.php',
        eventColor: '#667eea',
        eventTextColor: '#fff',
        eventDisplay: 'block',
        height: 'auto',
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        eventDidMount: function(info) {
            info.el.setAttribute('title', info.event.extendedProps.description);
        }
        // …or use the debug callback shown above if you prefer…
    });
    calendar.render();
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>