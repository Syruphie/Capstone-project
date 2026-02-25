<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'classes/Queue.php';
require_once 'classes/Equipment.php'; 

$user = new User(); 

// Check if user is logged in
if (!$user->isLoggedIn()) {
    header('Location: login.php'); 
    exit;
} 

$userRole = $user->getRole();
$userName = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];

// Initialize classes
$order = new Order();
$queue = new Queue();
$equipment = new Equipment();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
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
                    <h2>My Orders</h2>
                    <p>View and track your chemical compound orders</p>
                    <div class="card-stats">
                        <span class="stat"><?php echo $activeCount; ?> Active</span>
                    </div>
                    <a href="my-orders.php" class="btn btn-primary">View Orders</a>
                </div>

                <div class="dashboard-card">
                    <h2>Submit New Order</h2>
                    <p>Submit a new chemical testing request</p>
                    <a href="create-order.php" class="btn btn-primary">New Order</a>
                </div>

                <div class="dashboard-card">
                    <h2>Completed Orders</h2>
                    <p>View completed orders and test results</p>
                    <div class="card-stats">
                        <span class="stat"><?php echo $completedCount; ?> Completed</span>
                    </div>
                    <a href="my-orders.php" class="btn btn-secondary">View History</a>
                </div>

                <div class="dashboard-card">
                    <h2>Account Settings</h2>
                    <p>Update your profile and preferences</p>
                    <a href="#" class="btn btn-secondary">Settings</a>
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
                                        <td><?php echo htmlspecialchars($co['order_number']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($co['created_at'])); ?></td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $co['priority']; ?>">
                                                <?php echo ucfirst($co['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $co['sample_count']; ?></td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $co['status'] === 'submitted' ? 'priority' : ($co['status'] === 'completed' ? 'standard' : 'standard'); ?>" style="<?php
                                                if ($co['status'] === 'approved') echo 'background: #d1ecf1; color: #0c5460;';
                                                elseif ($co['status'] === 'processing') echo 'background: #e7e3ff; color: #5a4fcf;';
                                                elseif ($co['status'] === 'completed') echo 'background: #d4edda; color: #155724;';
                                                elseif ($co['status'] === 'rejected') echo 'background: #f8d7da; color: #721c24;';
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
                    <h2>Pending Samples</h2>
                    <p>Samples waiting for preparation or testing</p>
                    <div class="card-stats">
                        <span class="stat">0 Pending</span>
                    </div>
                    <a href="#" class="btn btn-secondary">View Samples</a>
                </div>

                <div class="dashboard-card">
                    <h2>Equipment Status</h2>
                    <p>Monitor laboratory equipment availability</p>
                    <?php
                    $equipmentList = $equipment->getAllEquipment(true);
                    $availableCount = count($equipmentList);
                    ?>
                    <div class="card-stats">
                        <span class="stat"><?php echo $availableCount; ?> Available</span>
                    </div>
                    <a href="#" class="btn btn-secondary">View Equipment</a>
                </div>

                <div class="dashboard-card">
                    <h2>Processing Queue</h2>
                    <p>View and manage the sample processing queue</p>
                    <?php
                    $standardQueue = $queue->getStandardQueue();
                    $queueCount = count($standardQueue);
                    ?>
                    <div class="card-stats">
                        <span class="stat"><?php echo $queueCount; ?> in Queue</span>
                    </div>
                    <a href="#" class="btn btn-secondary">View Queue</a>
                </div>

                <div class="dashboard-card">
                    <h2>Log Delay</h2>
                    <p>Report equipment delays or issues</p>
                    <a href="#" class="btn btn-warning">Log Delay</a>
                </div>
            </div>

        <?php elseif ($userRole === 'administrator'): ?>
            <!-- Administrator Dashboard -->
            <?php $pendingOrders = $order->getPendingOrders(); ?>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2>Pending Approvals</h2>
                    <p>Orders waiting for approval</p>
                    <div class="card-stats">
                        <span class="stat"><?php echo count($pendingOrders); ?> Pending</span>
                    </div>
                    <a href="admin.php?tab=approvals" class="btn btn-primary">Review Orders</a>
                </div>

                <div class="dashboard-card">
                    <h2>User Management</h2>
                    <p>Manage user accounts and permissions</p>
                    <a href="#" class="btn btn-secondary">Manage Users</a>
                </div>

                <div class="dashboard-card">
                    <h2>Equipment Management</h2>
                    <p>Configure equipment settings and schedules</p>
                    <a href="#" class="btn btn-secondary">Manage Equipment</a>
                </div>

                <div class="dashboard-card">
                    <h2>Reports & Analytics</h2>
                    <p>View system statistics and performance</p>
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
                                        <td><?php echo htmlspecialchars($po['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($po['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($po['company_name'] ?? '-'); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($po['created_at'])); ?></td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $po['priority']; ?>">
                                                <?php echo ucfirst($po['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $po['sample_count']; ?></td>
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

        <!-- Order Calendar Section -->
<div class="dashboard-card full-width">
    <h2>Order Calendar</h2>
    <div id="calendar"></div>
</div>

        <!-- System Information Card -->
        <div class="dashboard-card system-info">
            <h3>System Information</h3>
            <p><strong>Project:</strong> Phase 3 Prototype</p>
            <p><strong>Status:</strong> Development</p>
            <p><strong>Note:</strong> This is a school project prototype demonstrating core functionality.</p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'chatbot/chat.php' ; ?> <!-- Chatbot feature --> 

    <script>
fetch("get_calendar_events.php")
.then(response => response.json())
.then(events => {
    let calendar = document.getElementById("calendar");

    if (!events.length) {
        calendar.innerHTML = "<p>No calendar events available.</p>";
        return;
    }

    events.forEach(event => {
        calendar.innerHTML += `
            <div class="calendar-event">
                <strong>${event.title}</strong><br>
                <small>Date: ${event.date}</small><br>
                ${event.description}
            </div>
        `;
    });
})
.catch(error => {
    console.error("Calendar error:", error);
});
</script>

    <script src="js/main.js"></script>
</body>
</html>
