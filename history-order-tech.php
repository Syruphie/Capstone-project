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

/* technician should only see approved standard + priority orders */
$approvedOrders = $orderObj->getApprovedOrdersForTechnician();

$standardCount = 0;
$priorityCount = 0;

foreach ($approvedOrders as $o) {
    $priority = strtolower($o['priority'] ?? 'standard');
    if ($priority === 'priority') {
        $priorityCount++;
    } else {
        $standardCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - <?php echo APP_NAME; ?></title>
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
            border-radius: 0px;
            padding: 32px;
            margin-bottom: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
        }

        .welcome-section h1 {
            font-size: 42px;
            font-weight: 900;
            margin: 0 0 10px;
            letter-spacing: -1px;
            color: var(--text);
        }

        .welcome-section p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.6;
            font-weight: 600;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26px;
        }

        .dashboard-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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
            line-height: 1.6;
        }

        .dashboard-card.stat-card {
            padding: 22px;
            min-height: 160px;
        }

        .dashboard-card.stat-card h2 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        .dashboard-card.stat-card p {
            margin: 0 0 14px;
            font-size: 14px;
        }

        .card-stats .stat {
            font-size: 28px;
            font-weight: 900;
            display: inline-block;
            margin-bottom: 0;
            color: #5b4ae6;
        }

        .stat.priority {
            color: #f59e0b;
        }

        .dashboard-card.full-width {
            grid-column: 1 / -1;
            min-height: auto;
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
            white-space: nowrap;
        }

        .dashboard-table td {
            padding: 16px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .dashboard-table tr:hover {
            background: rgba(91, 74, 230, .05);
        }

        .btn {
            border-radius: 0px;
            font-weight: 900;
            padding: 12px 16px;
            display: inline-block;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #5b4ae6, #6b5df6);
            color: #fff;
        }

        .priority-pill {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .5px;
            border: 1px solid rgba(0, 0, 0, .10);
        }

        .pill-standard {
            background: rgba(91, 74, 230, .10);
            color: #3b2fc9;
        }

        .pill-priority {
            background: rgba(245, 158, 11, .14);
            color: #92400e;
        }

        .empty-state {
            text-align: center;
            padding: 18px !important;
            font-weight: 900;
            color: var(--muted);
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
            <h1>Order History</h1>
            <p>Technician view of approved standard and priority orders.</p>
        </div>

        <div class="dashboard-grid">

            <div class="dashboard-card stat-card">
                <div>
                    <h2>Standard Approved</h2>
                    <p>Total approved standard orders.</p>
                </div>
                <div class="card-stats">
                    <div class="stat"><?php echo (int)$standardCount; ?></div>
                </div>
            </div>

            <div class="dashboard-card stat-card">
                <div>
                    <h2>Priority Approved</h2>
                    <p>Total approved priority orders.</p>
                </div>
                <div class="card-stats">
                    <div class="stat priority"><?php echo (int)$priorityCount; ?></div>
                </div>
            </div>

            <div class="dashboard-card full-width">
                <h2 style="margin-bottom:14px;">All Approved Orders</h2>

                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Submitted</th>
                            <th>Priority</th>
                            <th>Samples</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($approvedOrders)): ?>
                            <tr>
                                <td colspan="6" class="empty-state">No approved orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($approvedOrders as $o): ?>
                                <?php
                                    $priority = strtolower($o['priority'] ?? 'standard');
                                    $pillClass = ($priority === 'priority') ? 'pill-priority' : 'pill-standard';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($o['order_number'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($o['company_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                        echo !empty($o['created_at'])
                                            ? date('M d, Y H:i', strtotime($o['created_at']))
                                            : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <span class="priority-pill <?php echo $pillClass; ?>">
                                            <?php echo ucfirst($priority); ?>
                                        </span>
                                    </td>
                                    <td><?php echo (int)($o['sample_count'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>