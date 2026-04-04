<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'classes/Equipment.php';
require_once 'classes/Sample.php';
require_once 'classes/Queue.php';
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
$queue = new Queue();
$sample = new Sample();
$email = new Email();

// Handle approve/reject actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_order'])) {
        $orderId = intval($_POST['order_id']);
        $orderData = $order->getOrderWithCustomer($orderId);

        if ($order->approveOrder($orderId, $userId)) {
            $message = 'Order approved successfully';

            // Auto-schedule: add to queue, set equipment, estimated completion
            $samples = $sample->getSamplesByOrder($orderId);
            $equipmentList = $equipment->getAllEquipment(true);
            $priority = $orderData['priority'] ?? 'standard';

            if (!empty($equipmentList)) {
                $eq = $equipmentList[0];
                $eqId = (int) $eq['id'];
                $processingPer = (int) $eq['processing_time_per_sample'];
                $prepMins = 0;
                foreach ($samples as $s) {
                    $prepMins += (int) ($s['preparation_time'] ?? 0);
                }
                $testingMins = count($samples) * ($processingPer ?: 5);
                $durationMins = $prepMins + $testingMins;

                $lastEnd = $queue->getLastScheduledEnd($eqId);
                $base = $lastEnd ? strtotime($lastEnd) : time();
                $start = date('Y-m-d H:i:s', $base);
                $end = date('Y-m-d H:i:s', $base + $durationMins * 60);

                $queue->addToQueueScheduled($orderId, $eqId, $priority, $start, $end);
                $order->updateOrderStatus($orderId, 'in_queue');
                $order->updateEstimatedCompletion($orderId, $end);
                $message .= ' - Queued and scheduled';
            }

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
    } elseif (isset($_POST['change_role'])) {
        $targetUserId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $newRole = isset($_POST['role']) ? trim($_POST['role']) : '';
        if ($targetUserId && in_array($newRole, ['customer', 'technician', 'administrator'], true)) {
            if ($user->assignRole($targetUserId, $newRole)) {
                $message = 'User role updated.';
            } else {
                $message = 'Failed to update role.';
            }
        }
    }
}

// Get current tab
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'approvals';

// Users tab: search and filter
$userSearch = isset($_GET['user_search']) ? trim($_GET['user_search']) : '';
$userRoleFilter = isset($_GET['user_role']) ? trim($_GET['user_role']) : '';
$userStatusFilter = isset($_GET['user_status']) ? trim($_GET['user_status']) : '';
$userStatusActive = null;
if ($userStatusFilter === 'active') $userStatusActive = true;
elseif ($userStatusFilter === 'inactive') $userStatusActive = false;
$usersList = $user->getAllUsers($userRoleFilter ?: null, $userSearch ?: null, $userStatusActive);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo APP_NAME; ?></title>
<<<<<<< Updated upstream
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
=======
    <link rel="stylesheet" href="css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo ASSET_VERSION; ?>">
>>>>>>> Stashed changes
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
                    <div id="approvalsCalendar" style="max-width:900px;margin:20px auto 40px;
                        background:#fff;padding:15px;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.1);
                        min-height:300px;"></div>
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
                            <span class="badge status-pill <?php echo $eq['is_available'] ? 'available' : 'unavailable'; ?>">
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

<<<<<<< Updated upstream
=======
    <!-- Add Equipment Modal -->
    <div class="modal-overlay" id="addEquipmentModal" aria-hidden="true">
        <div class="modal" role="dialog" aria-labelledby="addEquipmentModalTitle">
            <h2 id="addEquipmentModalTitle">Add Equipment</h2>
            <form id="addEquipmentForm">
                <div class="form-group">
                    <label for="eq_name">Name *</label>
                    <input type="text" id="eq_name" name="name" required maxlength="20" placeholder="e.g. ICP Spectrometer">
                </div>
                <div class="form-group">
                    <label for="eq_type">Equipment Type *</label>
                    <input type="text" id="eq_type" name="equipment_type" required maxlength="20" placeholder="e.g. ICP, XRF">
                </div>
                <div class="form-group">
                    <label for="eq_processing">Processing Time per Sample (min) *</label>
                    <input type="number" id="eq_processing" name="processing_time_per_sample" required min="0" value="2">
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="eq_warmup">Warmup Time (min)</label>
                        <input type="number" id="eq_warmup" name="warmup_time" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="eq_capacity">Daily Capacity</label>
                        <input type="number" id="eq_capacity" name="daily_capacity" min="0" value="0">
                    </div>
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="eq_break_interval">Break Interval (samples)</label>
                        <input type="number" id="eq_break_interval" name="break_interval" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="eq_break_duration">Break Duration (min)</label>
                        <input type="number" id="eq_break_duration" name="break_duration" min="0" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label for="eq_last_maintenance">Last Maintenance (optional)</label>
                    <input type="date" id="eq_last_maintenance" name="last_maintenance">
                </div>
                <div class="form-group form-group-checkbox">
                    <label><input type="checkbox" name="is_available" id="eq_available" checked> Available</label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="addEquipmentCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Equipment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Equipment Modal -->
    <div class="modal-overlay" id="editEquipmentModal" aria-hidden="true">
        <div class="modal" role="dialog" aria-labelledby="editEquipmentModalTitle">
            <h2 id="editEquipmentModalTitle">Edit Equipment</h2>
            <form id="editEquipmentForm">
                <input type="hidden" id="eqe_id" name="id" value="">
                <div class="form-group">
                    <label for="eqe_name">Name *</label>
                    <input type="text" id="eqe_name" name="name" required maxlength="20" placeholder="e.g. ICP Spectrometer">
                </div>
                <div class="form-group">
                    <label for="eqe_type">Equipment Type *</label>
                    <input type="text" id="eqe_type" name="equipment_type" required maxlength="20" placeholder="e.g. ICP, XRF">
                </div>
                <div class="form-group">
                    <label for="eqe_processing">Processing Time per Sample (min) *</label>
                    <input type="number" id="eqe_processing" name="processing_time_per_sample" required min="0" value="2">
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="eqe_warmup">Warmup Time (min)</label>
                        <input type="number" id="eqe_warmup" name="warmup_time" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="eqe_capacity">Daily Capacity</label>
                        <input type="number" id="eqe_capacity" name="daily_capacity" min="0" value="0">
                    </div>
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="eqe_break_interval">Break Interval (samples)</label>
                        <input type="number" id="eqe_break_interval" name="break_interval" min="0" value="0">
                    </div>
                    <div class="form-group">
                        <label for="eqe_break_duration">Break Duration (min)</label>
                        <input type="number" id="eqe_break_duration" name="break_duration" min="0" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label for="eqe_last_maintenance">Last Maintenance (optional)</label>
                    <input type="date" id="eqe_last_maintenance" name="last_maintenance">
                </div>
                <div class="form-group form-group-checkbox">
                    <label><input type="checkbox" name="is_available" id="eqe_available" checked> Available</label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="editEquipmentCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
>>>>>>> Stashed changes

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

                    <?php if ($message && $currentTab === 'users'): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form method="get" action="admin.php" class="filter-bar">
                        <input type="hidden" name="tab" value="users">
                        <input type="text" name="user_search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($userSearch); ?>">
                        <select name="user_role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="customer" <?php echo $userRoleFilter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="technician" <?php echo $userRoleFilter === 'technician' ? 'selected' : ''; ?>>Technician</option>
                            <option value="administrator" <?php echo $userRoleFilter === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                        <select name="user_status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $userStatusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $userStatusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </form>

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
                                <?php if (empty($usersList)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No users found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($usersList as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo htmlspecialchars($u['company_name'] ?? '—'); ?></td>
                                        <td>
                                            <form method="post" action="admin.php?tab=users<?php echo $userSearch ? '&user_search=' . urlencode($userSearch) : ''; ?><?php echo $userRoleFilter ? '&user_role=' . urlencode($userRoleFilter) : ''; ?><?php echo $userStatusFilter ? '&user_status=' . urlencode($userStatusFilter) : ''; ?>" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>">
                                                <select name="role" class="form-control" style="width:auto; display:inline-block; padding:6px 8px;" onchange="this.form.submit()">
                                                    <option value="customer" <?php echo $u['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                    <option value="technician" <?php echo $u['role'] === 'technician' ? 'selected' : ''; ?>>Technician</option>
                                                    <option value="administrator" <?php echo $u['role'] === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                                                </select>
                                                <input type="hidden" name="change_role" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo !empty($u['is_active']) ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo !empty($u['is_active']) ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $u['last_login'] ? date('Y-m-d H:i', strtotime($u['last_login'])) : '—'; ?></td>
                                        <td class="actions">—</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

<<<<<<< Updated upstream
=======
            <?php elseif ($currentTab === 'catalogue'): ?>
                <!-- Order Catalogue – Admin CRUD for order types -->
                <section class="admin-section">
                    <div class="equipment-header">
                        <div>
                            <h1>Order Catalogue</h1>
                            <p class="section-desc">Create and manage order types that customers can select when placing orders. Each type has a configurable cost.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-small" id="addOrderTypeBtn">Add Order Type</button>
                    </div>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Cost</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="orderTypesTableBody">
                                <?php
                                $typesList = $orderType->getAll(false);
                                if (empty($typesList)):
                                ?>
                                <tr>
                                    <td colspan="6" class="empty-state">No order types. Add one to get started.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($typesList as $ot): ?>
                                    <tr data-id="<?php echo (int) $ot['id']; ?>">
                                        <td><?php echo htmlspecialchars($ot['name']); ?></td>
                                        <td><?php echo isset($ot['sample_type']) ? ucfirst($ot['sample_type']) : 'Ore'; ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($ot['description'] ?? '', 0, 60)); ?><?php echo mb_strlen($ot['description'] ?? '') > 60 ? '…' : ''; ?></td>
                                        <td><?php echo number_format((float) $ot['cost'], 2); ?></td>
                                        <td>
                                            <span class="badge status-pill <?php echo $ot['is_active'] ? 'available' : 'unavailable'; ?>">
                                                <?php echo $ot['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button type="button" class="btn btn-xs btn-secondary btn-edit-type" data-id="<?php echo (int) $ot['id']; ?>">Edit</button>
                                            <button type="button" class="btn btn-xs btn-danger btn-delete-type" data-id="<?php echo (int) $ot['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <!-- Add/Edit Order Type Modal -->
                <div class="modal-overlay" id="orderTypeModal" aria-hidden="true">
                    <div class="modal" role="dialog">
                        <h2 id="orderTypeModalTitle">Add Order Type</h2>
                        <form id="orderTypeForm">
                            <input type="hidden" id="ot_id" name="id" value="">
                            <div class="form-group">
                                <label for="ot_name">Name *</label>
                                <input type="text" id="ot_name" name="name" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="ot_description">Description</label>
                                <textarea id="ot_description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="ot_sample_type">Sample Type *</label>
                                <select id="ot_sample_type" name="sample_type" required>
                                    <option value="ore">Ore (30 min prep)</option>
                                    <option value="liquid">Liquid (no prep)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ot_cost">Cost (<?php echo htmlspecialchars('$'); ?>) *</label>
                                <input type="number" id="ot_cost" name="cost" required min="0" step="0.01" value="0">
                            </div>
                            <div class="form-group form-group-checkbox" id="ot_activeWrap">
                                <label><input type="checkbox" name="is_active" id="ot_active" checked> Active</label>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn btn-secondary" id="orderTypeModalCancel">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

>>>>>>> Stashed changes
            <?php endif; ?>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
<<<<<<< Updated upstream
    <script src="js/main.js"></script>
=======
    <script src="js/main.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <?php if ($currentTab === 'equipment'): ?>
>>>>>>> Stashed changes
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calEl = document.getElementById('approvalsCalendar');
        if (calEl) {
            var cal = new FullCalendar.Calendar(calEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {left:'prev,next today',center:'title',right:'dayGridMonth,timeGridWeek,timeGridDay'},
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
                },
                noEventsContent: 'No orders scheduled'
            });
            cal.render();
        }
    });
    </script>
</body>
</html>