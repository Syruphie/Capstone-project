<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'classes/Equipment.php';
require_once 'classes/Sample.php';
require_once 'classes/Email.php';

$user = new User();

// Check if user is logged in and is administrator
if (!$user->isLoggedIn() || $user->getRole() !== 'administrator') {
    header('Location: login.php');
    exit;
}

$userName = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];

// Initialize classes
$order = new Order();
$equipment = new Equipment();
$email = new Email();

// Handle approve/reject actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_order'])) {
        $orderId = intval($_POST['order_id']);
        // Get order with customer info before approving
        $orderData = $order->getOrderWithCustomer($orderId);

        if ($order->approveOrder($orderId, $userId)) {
            $message = 'Order approved successfully';

            // Send email notification to customer
            if ($orderData && !empty($orderData['customer_email'])) {
                $emailSent = $email->sendOrderApprovalNotification(
                    $orderData['customer_email'],
                    $orderData['customer_name'],
                    $orderData['order_number']
                );
                if ($emailSent) {
                    $message .= ' - Email notification sent to customer';
                }
            }
        }
    } elseif (isset($_POST['reject_order'])) {
        $orderId = intval($_POST['order_id']);
        $reason = trim($_POST['rejection_reason'] ?? 'No reason provided');
        // Get order with customer info before rejecting
        $orderData = $order->getOrderWithCustomer($orderId);

        if ($order->rejectOrder($orderId, $reason)) {
            $message = 'Order rejected';

            // Send email notification to customer
            if ($orderData && !empty($orderData['customer_email'])) {
                $emailSent = $email->sendOrderRejectionNotification(
                    $orderData['customer_email'],
                    $orderData['customer_name'],
                    $orderData['order_number'],
                    $reason
                );
                if ($emailSent) {
                    $message .= ' - Email notification sent to customer';
                }
            }
        }
    }
}

// Get current tab
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'approvals';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-container">
        <main class="admin-content">
            <?php if ($currentTab === 'approvals'): ?>
                <!-- Pending Approvals Section -->
                <section class="admin-section">
                    <h1>Pending Approvals</h1>
                    <p class="section-desc">Review and approve or reject submitted orders.</p>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Company</th>
                                    <th>Submitted</th>
                                    <th>Priority</th>
                                    <th>Samples</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $pendingOrders = $order->getPendingOrders();
                                if (empty($pendingOrders)): 
                                ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No pending orders</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingOrders as $po): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($po['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($po['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($po['company_name'] ?? '-'); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($po['created_at'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $po['priority']; ?>">
                                                <?php echo ucfirst($po['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $po['sample_count']; ?></td>
                                        <td class="actions">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $po['id']; ?>">
                                                <button type="submit" name="approve_order" class="btn btn-small btn-success">Approve</button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $po['id']; ?>">
                                                <input type="hidden" name="rejection_reason" value="Order rejected by administrator">
                                                <button type="submit" name="reject_order" class="btn btn-small btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Manage Equipment page --> 
           <?php elseif ($currentTab === 'equipment'): ?>
            
<section class="admin-section">

    <!-- Header row -->
    <div class="equipment-header">
        <div>
            <h1>Manage Equipment</h1>
            <p class="section-desc">Configure equipment settings, processing times, and schedules.</p>
        </div>

        <button class="btn btn-primary btn-small add-equipment-btn">Add Equipment</button>
    </div>

    <!-- Table -->
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Processing</th>
                    <th>Warmup</th>
                    <th>Break</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $equipmentList = $equipment->getAllEquipment();
                if (empty($equipmentList)):
                ?>
                <tr>
                    <td colspan="8" class="empty-state">No equipment configured</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($equipmentList as $eq): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($eq['name']); ?></td>
                        <td><?php echo htmlspecialchars($eq['equipment_type']); ?></td>
                        <td><?php echo $eq['processing_time_per_sample']; ?> min</td>
                        <td><?php echo $eq['warmup_time']; ?> min</td>
                        <td><?php echo $eq['break_interval']; ?></td>
                        <td><?php echo $eq['daily_capacity']; ?></td>
                        <td>
                            <span class="status-pill <?php echo $eq['is_available'] ? 'available' : 'unavailable'; ?>">
                                <?php echo $eq['is_available'] ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="btn btn-xs btn-secondary">Edit</button>
                            <button class="btn btn-xs btn-warning">Delay</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>


            <?php elseif ($currentTab === 'samples'): ?>
                <!-- Manage Samples Section -->
                <section class="admin-section">
                    <h1>Manage Samples</h1>
                    <p class="section-desc">View and manage sample processing status.</p>
                    
                    <div class="filter-bar">
                        <select class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready">Ready</option>
                            <option value="testing">Testing</option>
                            <option value="completed">Completed</option>
                        </select>
                        <select class="form-control">
                            <option value="">All Types</option>
                            <option value="ore">Ore</option>
                            <option value="liquid">Liquid</option>
                        </select>
                        <button class="btn btn-secondary">Filter</button>
                    </div>

                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Sample ID</th>
                                    <th>Order #</th>
                                    <th>Type</th>
                                    <th>Compound</th>
                                    <th>Quantity</th>
                                    <th>Prep Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="empty-state">No samples found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

            <?php elseif ($currentTab === 'users'): ?>
                <!-- Manage Users Section -->
                <section class="admin-section">
                    <h1>Manage Users</h1>
                    <p class="section-desc">Create, modify, and manage user accounts and permissions.</p>
                    
                    <div class="admin-actions-bar">
                        <button class="btn btn-primary">Add User</button>
                        <button class="btn btn-secondary">Import CSV</button>
                    </div>

                    <div class="filter-bar">
                        <input type="text" class="form-control" placeholder="Search by name or email...">
                        <select class="form-control">
                            <option value="">All Roles</option>
                            <option value="customer">Customer</option>
                            <option value="technician">Technician</option>
                            <option value="administrator">Administrator</option>
                        </select>
                        <select class="form-control">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button class="btn btn-secondary">Search</button>
                    </div>

                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="empty-state">Loading users...</td>
                                </tr>
                                <!-- Prototype rows would be populated here -->
                            </tbody>
                        </table>
                    </div>
                </section>

            <?php elseif ($currentTab === 'reports'): ?>
                <!-- Performance Reports Section -->
                <section class="admin-section">
                    <h1>Performance Reports</h1>
                    <p class="section-desc">Generate and view reports on orders, revenue, and system performance.</p>
                    
                    <div class="report-cards">
                        <div class="report-card">
                            <h3>Orders Report</h3>
                            <p>View order statistics, processing times, and completion rates.</p>
                            <div class="report-options">
                                <select class="form-control">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button class="btn btn-primary">Generate</button>
                            </div>
                        </div>

                        <div class="report-card">
                            <h3>Revenue Report</h3>
                            <p>View payment statistics, revenue trends, and financial summaries.</p>
                            <div class="report-options">
                                <select class="form-control">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button class="btn btn-primary">Generate</button>
                            </div>
                        </div>

                        <div class="report-card">
                            <h3>Equipment Performance</h3>
                            <p>View equipment utilization, delays, and maintenance history.</p>
                            <div class="report-options">
                                <select class="form-control">
                                    <option value="">All Equipment</option>
                                    <?php foreach ($equipment->getAllEquipment() as $eq): ?>
                                    <option value="<?php echo $eq['id']; ?>"><?php echo htmlspecialchars($eq['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary">Generate</button>
                            </div>
                        </div>

                        <div class="report-card">
                            <h3>Queue Analytics</h3>
                            <p>View queue statistics, wait times, and processing efficiency.</p>
                            <div class="report-options">
                                <select class="form-control">
                                    <option value="all">All Queues</option>
                                    <option value="standard">Standard Queue</option>
                                    <option value="priority">Priority Queue</option>
                                </select>
                                <button class="btn btn-primary">Generate</button>
                            </div>
                        </div>
                    </div>

                    <!-- Report Output Area -->
                    <div class="report-output">
                        <p class="empty-state">Select a report type and click Generate to view results.</p>
                    </div>
                </section>

            <?php endif; ?>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>