<?php
require_once 'config/database.php';
require_once 'src/classes/Frontend/bootstrap.php';

$user = new FrontendUser();

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$role = $user->getRole();

// Technicians do not have access to Order History
if ($role === 'technician') {
    header('Location: dashboard.php');
    exit;
}

$order = new FrontendOrder();
$searchOrderNumber = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';
$searchDateFrom   = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$searchDateTo     = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$searchCustomerName = isset($_GET['customer_name']) ? trim($_GET['customer_name']) : '';

if ($role === 'customer') {
    $orders = $order->getOrderHistoryForCustomer($_SESSION['user_id'], $searchOrderNumber, $searchDateFrom, $searchDateTo);
    $standardOrders = array_filter($orders, function ($o) { return ($o['priority'] ?? '') === 'standard'; });
    $prioritizedOrders = array_filter($orders, function ($o) { return ($o['priority'] ?? '') === 'prioritized'; });
} else {
    $orders = $order->getOrderHistoryForAdmin($searchCustomerName, $searchOrderNumber, $searchDateFrom, $searchDateTo);
    $standardOrders = array_filter($orders, function ($o) { return ($o['priority'] ?? '') === 'standard'; });
    $prioritizedOrders = array_filter($orders, function ($o) { return ($o['priority'] ?? '') === 'prioritized'; });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .orders-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .orders-header { background: white; border-radius: 10px; padding: 25px 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .orders-header h1 { color: #333; margin-bottom: 5px; }
        .orders-header p { color: #666; margin: 0; }
        .search-form { background: white; border-radius: 10px; padding: 20px 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-form form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .search-form label { display: block; font-size: 12px; color: #666; margin-bottom: 4px; }
        .search-form input[type="text"], .search-form input[type="date"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; min-width: 140px; }
        .search-form button { padding: 8px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .search-form button[type="reset"] { background: #6c757d; }
        .history-section { background: white; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .history-section h2 { color: #333; margin-bottom: 15px; font-size: 18px; }
        .admin-table-container { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #f8f9fa; font-weight: 600; color: #333; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-results_available, .status-completed { background: #d4edda; color: #155724; }
        .badge-standard { background: #e9ecef; color: #495057; }
        .badge-prioritized { background: #fff3cd; color: #856404; }
        .empty-history { text-align: center; padding: 40px 20px; color: #666; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="orders-container">
        <div class="orders-header">
            <h1>Order History</h1>
            <p><?php echo $role === 'customer' ? 'Your completed orders' : 'All completed orders'; ?></p>
        </div>

        <div class="search-form">
            <form method="get" action="order-history.php">
                <?php if ($role === 'administrator'): ?>
                    <div>
                        <label>Customer name</label>
                        <input type="text" name="customer_name" value="<?php echo htmlspecialchars($searchCustomerName); ?>" placeholder="Name or email">
                    </div>
                <?php endif; ?>
                <div>
                    <label>Order number</label>
                    <input type="text" name="order_number" value="<?php echo htmlspecialchars($searchOrderNumber); ?>" placeholder="e.g. ORD-">
                </div>
                <div>
                    <label>From date</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($searchDateFrom); ?>">
                </div>
                <div>
                    <label>To date</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($searchDateTo); ?>">
                </div>
                <div>
                    <button type="submit">Search</button>
                    <button type="reset" onclick="window.location='order-history.php';">Clear</button>
                </div>
            </form>
        </div>

        <?php if (empty($orders)): ?>
            <div class="history-section">
                <div class="empty-history">
                    <h3>No completed orders found</h3>
                    <p><?php echo $role === 'customer' ? 'You have no finished orders yet, or no orders match your search.' : 'No finished orders match your search.'; ?></p>
                </div>
            </div>
        <?php else: ?>
            <?php if (!empty($prioritizedOrders)): ?>
            <div class="history-section">
                <h2>Prioritized orders</h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <?php if ($role === 'administrator'): ?><th>Customer</th><?php endif; ?>
                                <th>Completed / Date</th>
                                <th>Samples</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prioritizedOrders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                                <?php if ($role === 'administrator'): ?>
                                    <td><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                                <?php endif; ?>
                                <td><?php echo $o['completed_at'] ? date('M d, Y H:i', strtotime($o['completed_at'])) : (isset($o['estimated_completion']) && $o['estimated_completion'] ? date('M d, Y', strtotime($o['estimated_completion'])) : '-'); ?></td>
                                <td><?php echo (int)($o['sample_count'] ?? 0); ?></td>
                                <td><span class="status-badge status-<?php echo $o['status'] ?? 'completed'; ?>"><?php echo ucfirst(str_replace('_', ' ', $o['status'] ?? 'Completed')); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($standardOrders)): ?>
            <div class="history-section">
                <h2>Standard orders</h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <?php if ($role === 'administrator'): ?><th>Customer</th><?php endif; ?>
                                <th>Completed / Date</th>
                                <th>Samples</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($standardOrders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                                <?php if ($role === 'administrator'): ?>
                                    <td><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                                <?php endif; ?>
                                <td><?php echo $o['completed_at'] ? date('M d, Y H:i', strtotime($o['completed_at'])) : (isset($o['estimated_completion']) && $o['estimated_completion'] ? date('M d, Y', strtotime($o['estimated_completion'])) : '-'); ?></td>
                                <td><?php echo (int)($o['sample_count'] ?? 0); ?></td>
                                <td><span class="status-badge status-<?php echo $o['status'] ?? 'completed'; ?>"><?php echo ucfirst(str_replace('_', ' ', $o['status'] ?? 'Completed')); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>

