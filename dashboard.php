<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'classes/Queue.php';
require_once 'classes/Equipment.php';

$user = new User();


if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userRole = $user->getRole();
$userName = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];


$order = new Order();
$queue = new Queue();
$equipment = new Equipment();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard -
        <?php echo APP_NAME; ?>
    </title>
    <link rel="stylesheet" href="css/style.css">


    <style>
        :root {
            --text: #0f172a;
            --muted: #64748b;
            --border: rgba(15, 23, 42, .10);
        }

        /* MAIN WRAPPER */
        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        /* WELCOME HEADER */
        .welcome-section {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            /* sharp */
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

        .role-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            /* rounded inside */
            font-size: 12px;
            font-weight: 900;
        }

        /* GRID */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26px;
        }

        /* CARDS */
        .dashboard-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            /* sharp outside */
            padding: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            min-height: 230px;
            /* same size */
        }

        .dashboard-card h2 {
            font-size: 22px;
            font-weight: 900;
            margin-bottom: 10px;
            color: var(--text);
        }

        .dashboard-card p {
            font-size: 15px;
            color: var(--muted);
            margin-bottom: 18px;
            line-height: 1.6;
        }

        /* STATS */
        .card-stats .stat {
            font-size: 28px;
            font-weight: 900;
            color: #5b4ae6;
            display: inline-block;
            margin-bottom: 18px;
        }

        /* FULL WIDTH */
        .dashboard-card.full-width {
            grid-column: 1 / -1;
            min-height: auto;
            /* allow table to size naturally */
        }

        /* TABLE */
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

        /* BUTTONS (rounded inside feel) */
        .btn {
            border-radius: 16px;
            font-weight: 900;
            padding: 12px 16px;
        }

        /* SYSTEM INFO */
        .dashboard-card.system-info {
            margin-top: 30px;
        }

        /* RESPONSIVE */
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
            <h1>Welcome,
                <?php echo htmlspecialchars($userName); ?>!
            </h1>
            <p class="role-badge role-<?php echo $userRole; ?>">
                <?php echo ucfirst($userRole); ?>
            </p>
        </div>

        <?php if ($userRole === 'customer'): ?>
            <!-- Customer Dashboard -->
            <?php
            $customerOrders = $order->getOrdersByCustomer($userId);
            $activeCount = 0;
            $completedCount = 0;
            foreach ($customerOrders as $co) {
                if (in_array($co['status'], ['submitted', 'approved', 'processing'])) {
                    $activeCount++;
                } elseif ($co['status'] === 'completed') {
                    $completedCount++;
                }
            }
            ?>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div>
                        <h2>My Orders</h2>
                        <p>View and track your chemical compound orders</p>
                        <div class="card-stats">
                            <span class="stat">
                                <?php echo $activeCount; ?> Active
                            </span>
                        </div>
                    </div>
                    <a href="my-orders.php" class="btn btn-primary">View Orders</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Submit New Order</h2>
                        <p>Submit a new chemical testing request</p>
                    </div>
                    <a href="create-order.php" class="btn btn-primary">New Order</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Completed Orders</h2>
                        <p>View completed orders and test results</p>
                        <div class="card-stats">
                            <span class="stat">
                                <?php echo $completedCount; ?> Completed
                            </span>
                        </div>
                    </div>

                    <a href="history-order-Cus.php" class="btn btn-primary">View History</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Account Settings</h2>
                        <p>Update your profile and preferences</p>
                    </div>
                    <a href="account-settings-customer.php" class="btn btn-primary">Settings</a>
                </div>

                <div class="dashboard-card full-width">
                    <h2>Recent Orders</h2>
                    <div class="activity-list">
                        <?php if (empty($customerOrders)): ?>
                            <p>No orders yet. <a href="create-order.php">Create your first order</a></p>
                        <?php else: ?>
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Submitted</th>
                                        <th>Priority</th>
                                        <th>Samples</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($customerOrders, 0, 5) as $co): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($co['order_number']); ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($co['created_at'])); ?>
                                            </td>
                                            <td>
                                                <span class="priority-badge priority-<?php echo $co['priority']; ?>">
                                                    <?php echo ucfirst($co['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $co['sample_count']; ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="priority-badge priority-<?php echo $co['status'] === 'submitted' ? 'priority' : 'standard'; ?>"
                                                    style="<?php
                                                    if ($co['status'] === 'approved')
                                                        echo 'background: #d1ecf1; color: #0c5460;';
                                                    elseif ($co['status'] === 'processing')
                                                        echo 'background: #e7e3ff; color: #5a4fcf;';
                                                    elseif ($co['status'] === 'completed')
                                                        echo 'background: #d4edda; color: #155724;';
                                                    elseif ($co['status'] === 'rejected')
                                                        echo 'background: #f8d7da; color: #721c24;';
                                                    ?>">
                                                    <?php echo ucfirst($co['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if (count($customerOrders) > 5): ?>
                                <div style="margin-top: 15px;">
                                    <a href="my-orders.php" class="btn btn-secondary">View All Orders</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($userRole === 'technician'): ?>
            <!-- Technician Dashboard -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div>
                        <h2>Pending Samples</h2>
                        <p>Samples waiting for preparation or testing</p>
                        <div class="card-stats">
                            <span class="stat">0 Pending</span>
                        </div>
                    </div>
                    <a href="#" class="btn btn-secondary">View Samples</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Equipment Status</h2>
                        <p>Monitor laboratory equipment availability</p>
                        <?php
                        $equipmentList = $equipment->getAllEquipment(true);
                        $availableCount = count($equipmentList);
                        ?>
                        <div class="card-stats">
                            <span class="stat">
                                <?php echo $availableCount; ?> Available
                            </span>
                        </div>
                    </div>
                    <a href="#" class="btn btn-secondary">View Equipment</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Processing Queue</h2>
                        <p>View and manage the sample processing queue</p>
                        <?php
                        $standardQueue = $queue->getStandardQueue();
                        $queueCount = count($standardQueue);
                        ?>
                        <div class="card-stats">
                            <span class="stat">
                                <?php echo $queueCount; ?> in Queue
                            </span>
                        </div>
                    </div>
                    <a href="#" class="btn btn-secondary">View Queue</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Log Delay</h2>
                        <p>Report equipment delays or issues</p>
                    </div>
                    <a href="#" class="btn btn-warning">Log Delay</a>
                </div>
            </div>

        <?php elseif ($userRole === 'administrator'): ?>
            <!-- Administrator Dashboard -->
            <?php $pendingOrders = $order->getPendingOrders(); ?>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div>
                        <h2>Pending Approvals</h2>
                        <p>Orders waiting for approval</p>
                        <div class="card-stats">
                            <span class="stat">
                                <?php echo count($pendingOrders); ?> Pending
                            </span>
                        </div>
                    </div>
                    <a href="admin.php?tab=approvals" class="btn btn-primary">Review Orders</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>User Management</h2>
                        <p>Manage user accounts and permissions</p>
                    </div>
                    <a href="#" class="btn btn-secondary">Manage Users</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Equipment Management</h2>
                        <p>Configure equipment settings and schedules</p>
                    </div>
                    <a href="#" class="btn btn-secondary">Manage Equipment</a>
                </div>

                <div class="dashboard-card">
                    <div>
                        <h2>Reports & Analytics</h2>
                        <p>View system statistics and performance</p>
                    </div>
                    <a href="#" class="btn btn-secondary">View Reports</a>
                </div>

                <div class="dashboard-card full-width">
                    <h2>Pending Orders</h2>
                    <div class="activity-list">
                        <?php if (empty($pendingOrders)): ?>
                            <p>No pending orders</p>
                        <?php else: ?>
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
                                    <?php foreach ($pendingOrders as $po): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($po['order_number']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($po['customer_name']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($po['company_name'] ?? '-'); ?>
                                            </td>
                                            <td>
                                                <?php echo date('Y-m-d H:i', strtotime($po['created_at'])); ?>
                                            </td>
                                            <td>
                                                <span class="priority-badge priority-<?php echo $po['priority']; ?>">
                                                    <?php echo ucfirst($po['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $po['sample_count']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div style="margin-top: 15px;">
                                <a href="admin.php?tab=approvals" class="btn btn-primary">Go to Approvals</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- System Information Card -->
        <div class="dashboard-card system-info">
            <h3 style="margin:0 0 10px; font-size:20px; font-weight:900;">System Information</h3>
            <p><strong>Project:</strong> Phase 3 Prototype</p>
            <p><strong>Status:</strong> Development</p>
            <p><strong>Note:</strong> This is a school project prototype demonstrating core functionality.</p>
        </div>

    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>

</html>