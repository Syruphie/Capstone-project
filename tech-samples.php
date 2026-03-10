<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

if (!$user->isLoggedIn() || $user->getRole() !== 'technician') {
    header('Location: login.php');
    exit;
}

$orderObj = new Order();

/* Approved orders only, technician-facing */
$ordersAll = [];
if (method_exists($orderObj, 'getApprovedOrdersForTechnician')) {
    $ordersAll = $orderObj->getApprovedOrdersForTechnician();
} elseif (method_exists($orderObj, 'getOrderHistoryForTechnician')) {
    foreach ($orderObj->getOrderHistoryForTechnician() as $o) {
        if (strtolower($o['status'] ?? '') === 'approved') {
            $ordersAll[] = $o;
        }
    }
} elseif (method_exists($orderObj, 'getOrdersByStatus')) {
    $ordersAll = $orderObj->getOrdersByStatus('approved');
}

$totalOrders = count($ordersAll);
$totalSamples = 0;
$priorityOrders = 0;

foreach ($ordersAll as $o) {
    $totalSamples += (int)($o['sample_count'] ?? 0);
    if (strtolower($o['priority'] ?? '') === 'priority') {
        $priorityOrders++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samples - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        :root {
            --text: #0f172a;
            --muted: #64748b;
            --border: rgba(15, 23, 42, .10);
        }

        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        .welcome-section {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0;
            padding: 32px;
            margin-bottom: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
        }

        .welcome-section h1 {
            font-size: 42px;
            font-weight: 900;
            margin: 0 0 10px;
            color: var(--text);
        }

        .welcome-section p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            font-weight: 600;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 26px;
            margin-bottom: 26px;
        }

        .dashboard-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
        }

        .dashboard-card h2 {
            font-size: 22px;
            font-weight: 900;
            margin: 0 0 10px;
            color: var(--text);
        }

        .dashboard-card p {
            font-size: 15px;
            color: var(--muted);
            margin: 0 0 18px;
        }

        .stat {
            font-size: 28px;
            font-weight: 900;
            color: #5b4ae6;
        }

        .table-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
        }

        .table-card h2 {
            margin: 0 0 14px;
            font-size: 24px;
            font-weight: 900;
            color: var(--text);
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .dashboard-table th {
            text-align: left;
            padding: 14px;
            font-weight: 900;
            border-bottom: 1px solid var(--border);
            background: #fafafa;
            color: var(--muted);
        }

        .dashboard-table td {
            padding: 16px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        .dashboard-table tr:hover {
            background: rgba(91, 74, 230, .05);
        }

        .priority-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 0;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .priority-standard {
            background: #e9ecef;
            color: #495057;
        }

        .priority-priority {
            background: #fff3cd;
            color: #856404;
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            font-weight: 700;
            padding: 20px !important;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Samples</h1>
            <p>Technician view of approved orders and their sample counts.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Approved Orders</h2>
                <p>Orders ready for technician processing.</p>
                <div class="stat"><?php echo $totalOrders; ?></div>
            </div>

            <div class="dashboard-card">
                <h2>Total Samples</h2>
                <p>Total samples across approved orders.</p>
                <div class="stat"><?php echo $totalSamples; ?></div>
            </div>

            <div class="dashboard-card">
                <h2>Priority Orders</h2>
                <p>Approved orders marked as priority.</p>
                <div class="stat"><?php echo $priorityOrders; ?></div>
            </div>
        </div>

        <div class="table-card">
            <h2>Approved Order Samples</h2>

            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Company</th>
                        <th>Priority</th>
                        <th>Samples</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordersAll)): ?>
                        <tr>
                            <td colspan="6" class="empty-state">No approved sample orders found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ordersAll as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($o['company_name'] ?? '-'); ?></td>
                                <td>
                                    <span class="priority-badge priority-<?php echo strtolower($o['priority'] ?? 'standard'); ?>">
                                        <?php echo ucfirst($o['priority'] ?? 'standard'); ?>
                                    </span>
                                </td>
                                <td><?php echo (int)($o['sample_count'] ?? 0); ?></td>
                                <td>
                                    <?php
                                    echo !empty($o['created_at'])
                                        ? date('M d, Y H:i', strtotime($o['created_at']))
                                        : '-';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>