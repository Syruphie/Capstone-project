<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'classes/Equipment.php';
require_once 'classes/Sample.php';
require_once 'classes/Queue.php';
require_once 'classes/Email.php';
require_once 'classes/OrderType.php';

$user = new User();
$userRole = $user->getRole();

// Allow administrator or technician (technician only for approvals and equipment tabs)
if (!$user->isLoggedIn() || !in_array($userRole, ['administrator', 'technician'], true)) {
    header('Location: login.php');
    exit;
}

$userName = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];

// Technicians may only access the approvals tab (same page as admin for order approval)
$allowedTabsForTechnician = ['approvals'];
if ($userRole === 'technician') {
    $currentTabParam = isset($_GET['tab']) ? $_GET['tab'] : 'approvals';
    if (!in_array($currentTabParam, $allowedTabsForTechnician, true)) {
        header('Location: admin.php?tab=approvals');
        exit;
    }
}

// Initialize classes
$order = new Order();
$equipment = new Equipment();
$queue = new Queue();
$sample = new Sample();
$email = new Email();
$orderType = new OrderType();

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
    } elseif (isset($_POST['change_role']) && $userRole === 'administrator') {
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

// Get current tab (technician restricted above)
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : ($userRole === 'technician' ? 'approvals' : 'approvals');

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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="admin-container">
        <main class="admin-content">
            <?php if ($currentTab === 'approvals'): ?>
                <!-- Pending Approvals – same order approval page for Admin and Technician -->
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
                                                <input type="hidden" name="rejection_reason" value="Order rejected">
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

    <!-- Add Equipment Modal -->
    <div class="modal-overlay" id="addEquipmentModal" aria-hidden="true">
        <div class="modal" role="dialog" aria-labelledby="addEquipmentModalTitle">
            <h2 id="addEquipmentModalTitle">Add Equipment</h2>
            <form id="addEquipmentForm">
                <div class="form-group">
                    <label for="eq_name">Name *</label>
                    <input type="text" id="eq_name" name="name" required maxlength="255" placeholder="e.g. ICP Spectrometer">
                </div>
                <div class="form-group">
                    <label for="eq_type">Equipment Type *</label>
                    <input type="text" id="eq_type" name="equipment_type" required maxlength="100" placeholder="e.g. ICP, XRF">
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
                    <p class="section-desc">Generate and view reports on orders, revenue, equipment, and queue. Export as CSV or JSON.</p>
                    
                    <div class="report-cards">
                        <div class="report-card" data-report-type="orders">
                            <h3>Orders Report</h3>
                            <p>View order statistics, processing times, and completion rates.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>

                        <div class="report-card" data-report-type="revenue">
                            <h3>Revenue Report</h3>
                            <p>View payment statistics, revenue trends, and financial summaries.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>

                        <div class="report-card" data-report-type="equipment">
                            <h3>Equipment Report</h3>
                            <p>View equipment utilization, delays, and maintenance history.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>

                        <div class="report-card" data-report-type="queue">
                            <h3>Queue Analytics</h3>
                            <p>View queue statistics, wait times, and processing efficiency.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>
                    </div>

                    <div class="report-export-actions" id="reportExportActions" style="display:none;">
                        <button type="button" class="btn btn-small btn-primary" id="reportDownloadCsv">Download as CSV</button>
                        <button type="button" class="btn btn-small btn-primary" id="reportDownloadJson">Download as JSON</button>
                    </div>

                    <!-- Report Output Area -->
                    <div class="report-output" id="reportOutput">
                        <p class="empty-state">Select a report type and time range, then click Generate to view results.</p>
                    </div>
                </section>

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
                                            <span class="status-pill <?php echo $ot['is_active'] ? 'available' : 'unavailable'; ?>">
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

            <?php endif; ?>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
    <?php if ($currentTab === 'equipment'): ?>
    <script>
    (function() {
        var addBtn = document.querySelector('.add-equipment-btn');
        var modal = document.getElementById('addEquipmentModal');
        var form = document.getElementById('addEquipmentForm');
        var cancelBtn = document.getElementById('addEquipmentCancel');
        if (!addBtn || !modal || !form) return;
        function openModal() { modal.setAttribute('aria-hidden', 'false'); }
        function closeModal() { modal.setAttribute('aria-hidden', 'true'); }
        addBtn.addEventListener('click', openModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var fd = new FormData(form);
            var payload = {
                name: fd.get('name') || '',
                equipment_type: fd.get('equipment_type') || '',
                processing_time_per_sample: parseInt(fd.get('processing_time_per_sample'), 10) || 0,
                warmup_time: parseInt(fd.get('warmup_time'), 10) || 0,
                break_interval: parseInt(fd.get('break_interval'), 10) || 0,
                break_duration: parseInt(fd.get('break_duration'), 10) || 0,
                daily_capacity: parseInt(fd.get('daily_capacity'), 10) || 0,
                is_available: fd.get('is_available') === 'on',
                last_maintenance: fd.get('last_maintenance') || null
            };
            var submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            fetch('api/equipment-add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    closeModal();
                    window.location.reload();
                } else {
                    alert(res.error || 'Failed to add equipment');
                }
            })
            .catch(function() { alert('Request failed'); })
            .then(function() { submitBtn.disabled = false; });
        });
    })();
    </script>
    <?php endif; ?>
    <?php if ($currentTab === 'reports'): ?>
    <script>
    (function() {
        var reportOutput = document.getElementById('reportOutput');
        var reportExportActions = document.getElementById('reportExportActions');
        var reportDownloadCsv = document.getElementById('reportDownloadCsv');
        var reportDownloadJson = document.getElementById('reportDownloadJson');
        var currentReportData = null;

        document.querySelectorAll('.report-card').forEach(function(card) {
            var rangeSelect = card.querySelector('.report-range');
            var customDates = card.querySelector('.report-card-custom-dates');
            var fromInput = card.querySelector('.report-from');
            var toInput = card.querySelector('.report-to');
            rangeSelect.addEventListener('change', function() {
                customDates.style.display = this.value === 'custom' ? 'block' : 'none';
            });
            card.querySelector('.btn-report-generate').addEventListener('click', function() {
                var range = rangeSelect.value;
                if (range === 'custom' && (!fromInput.value || !toInput.value)) {
                    alert('Please select custom date range.');
                    return;
                }
                runReport(card.dataset.reportType, range, fromInput.value, toInput.value);
            });
        });

        function buildUrl(type, range, fromVal, toVal) {
            var params = new URLSearchParams();
            params.set('type', type);
            params.set('range', range);
            if (range === 'custom') {
                params.set('from', fromVal || '');
                params.set('to', toVal || '');
            }
            return 'api/reports.php?' + params.toString();
        }

        function runReport(type, range, fromVal, toVal) {
            reportOutput.innerHTML = '<p class="empty-state">Loading…</p>';
            reportExportActions.style.display = 'none';
            fetch(buildUrl(type, range, fromVal, toVal)).then(function(r) { return r.json(); }).then(function(res) {
                if (!res.success) {
                    reportOutput.innerHTML = '<p class="empty-state">' + (res.error || 'Error') + '</p>';
                    return;
                }
                currentReportData = res;
                var html = '';
                switch (res.report) {
                    case 'orders': html = renderOrders(res); break;
                    case 'revenue': html = renderRevenue(res); break;
                    case 'equipment': html = renderEquipment(res); break;
                    case 'queue': html = renderQueue(res); break;
                    default: html = '<p>Unknown report</p>';
                }
                reportOutput.innerHTML = html;
                reportExportActions.style.display = 'block';
            }).catch(function() {
                reportOutput.innerHTML = '<p class="empty-state">Request failed.</p>';
            });
        }

        function renderOrders(data) {
            var s = data.statistics;
            var html = '<p><strong>Period:</strong> ' + (data.from || '') + ' to ' + (data.to || '') + '</p>';
            html += '<p><strong>Total orders:</strong> ' + (s.total || 0) + '</p>';
            if (s.by_status && s.by_status.length) {
                html += '<table class="admin-table"><thead><tr><th>Status</th><th>Count</th></tr></thead><tbody>';
                s.by_status.forEach(function(r) { html += '<tr><td>' + r.status + '</td><td>' + r.cnt + '</td></tr>'; });
                html += '</tbody></table>';
            }
            if (data.rows && data.rows.length) {
                html += '<h4>Orders</h4><table class="admin-table"><thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Priority</th><th>Total</th><th>Created</th></tr></thead><tbody>';
                data.rows.forEach(function(r) {
                    html += '<tr><td>' + (r.order_number||'') + '</td><td>' + (r.customer_name||'') + '</td><td>' + (r.status||'') + '</td><td>' + (r.priority||'') + '</td><td>' + (r.total_cost||'') + '</td><td>' + (r.created_at||'') + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            return html;
        }
        function renderRevenue(data) {
            var html = '<p><strong>Period:</strong> ' + (data.from || '') + ' to ' + (data.to || '') + '</p>';
            html += '<p><strong>Revenue:</strong> $' + parseFloat(data.revenue || 0).toFixed(2) + '</p>';
            html += '<p><strong>Orders (paid/confirmed):</strong> ' + (data.order_count || 0) + '</p>';
            if (data.rows && data.rows.length) {
                html += '<h4>Orders</h4><table class="admin-table"><thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Created</th></tr></thead><tbody>';
                data.rows.forEach(function(r) {
                    html += '<tr><td>' + (r.order_number||'') + '</td><td>' + (r.customer_name||'') + '</td><td>' + (r.status||'') + '</td><td>' + (r.total_cost||'') + '</td><td>' + (r.created_at||'') + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            return html;
        }
        function renderEquipment(data) {
            var html = '<p><strong>Equipment utilization, delays, and maintenance</strong></p>';
            if (data.equipment && data.equipment.length) {
                data.equipment.forEach(function(eq) {
                    html += '<div class="report-equipment-block"><h4>' + (eq.name||'') + ' (' + (eq.equipment_type||'') + ')</h4>';
                    html += '<p>Status: ' + (eq.is_available ? 'Available' : 'Unavailable') + ' | Last maintenance: ' + (eq.last_maintenance||'—') + ' | Delay count: ' + (eq.delay_count||0) + '</p>';
                    if (eq.delays && eq.delays.length) {
                        html += '<table class="admin-table"><thead><tr><th>Delay start</th><th>Duration (min)</th><th>Reason</th></tr></thead><tbody>';
                        eq.delays.forEach(function(d) { html += '<tr><td>' + (d.delay_start||'') + '</td><td>' + (d.delay_duration||'') + '</td><td>' + (d.reason||'') + '</td></tr>'; });
                        html += '</tbody></table>';
                    }
                    html += '</div>';
                });
            } else {
                html += '<p class="empty-state">No equipment data.</p>';
            }
            return html;
        }
        function renderQueue(data) {
            var s = data.statistics || {};
            var html = '<p><strong>Period:</strong> ' + (data.from || '') + ' to ' + (data.to || '') + '</p>';
            html += '<p><strong>Standard queue length:</strong> ' + (s.standard_queue_length||0) + ' | <strong>Priority queue length:</strong> ' + (s.priority_queue_length||0) + '</p>';
            html += '<p><strong>Average wait (minutes):</strong> ' + (s.average_wait_minutes||0) + '</p>';
            if (data.rows && data.rows.length) {
                html += '<h4>Queue entries</h4><table class="admin-table"><thead><tr><th>Order #</th><th>Equipment</th><th>Scheduled start</th><th>Scheduled end</th><th>Type</th></tr></thead><tbody>';
                data.rows.forEach(function(r) {
                    html += '<tr><td>' + (r.order_number||'') + '</td><td>' + (r.equipment_name||'') + '</td><td>' + (r.scheduled_start||'') + '</td><td>' + (r.scheduled_end||'') + '</td><td>' + (r.queue_type||'') + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            return html;
        }

            reportOutput.innerHTML = '<p class="empty-state">Loading…</p>';
            reportExportActions.style.display = 'none';
            fetch(buildUrl(type, range, fromVal, toVal)).then(function(r) { return r.json(); }).then(function(res) {
                if (!res.success) {
                    reportOutput.innerHTML = '<p class="empty-state">' + (res.error || 'Error') + '</p>';
                    return;
                }
                currentReportData = res;
                var html = '';
                switch (res.report) {
                    case 'orders': html = renderOrders(res); break;
                    case 'revenue': html = renderRevenue(res); break;
                    case 'equipment': html = renderEquipment(res); break;
                    case 'queue': html = renderQueue(res); break;
                    default: html = '<p>Unknown report</p>';
                }
                reportOutput.innerHTML = html;
                reportExportActions.style.display = 'block';
            }).catch(function() {
                reportOutput.innerHTML = '<p class="empty-state">Request failed.</p>';
            });
        }

        function exportCsv() {
            if (!currentReportData) return;
            var rows = currentReportData.rows || [];
            if (currentReportData.report === 'equipment' && currentReportData.equipment) {
                var lines = ['Name,Type,Available,Last Maintenance,Delay Count'];
                currentReportData.equipment.forEach(function(eq) {
                    lines.push([eq.name, eq.equipment_type, eq.is_available ? 'Yes' : 'No', eq.last_maintenance || '', eq.delay_count || 0].map(function(c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(','));
                });
                downloadFile(lines.join('\n'), 'equipment-report.csv', 'text/csv');
                return;
            }
            if (!rows.length) { downloadFile('No data', currentReportData.report + '-report.csv', 'text/csv'); return; }
            var headers = Object.keys(rows[0]);
            var lines = [headers.map(function(h) { return '"' + String(h).replace(/"/g, '""') + '"'; }).join(',')];
            rows.forEach(function(r) {
                lines.push(headers.map(function(h) { return '"' + String(r[h] || '').replace(/"/g, '""') + '"'; }).join(','));
            });
            downloadFile(lines.join('\n'), currentReportData.report + '-report.csv', 'text/csv');
        }
        function exportJson() {
            if (!currentReportData) return;
            downloadFile(JSON.stringify(currentReportData, null, 2), currentReportData.report + '-report.json', 'application/json');
        }
        function downloadFile(content, filename, mime) {
            var a = document.createElement('a');
            a.href = 'data:' + mime + ';charset=utf-8,' + encodeURIComponent(content);
            a.download = filename;
            a.click();
        }
        reportDownloadCsv.addEventListener('click', exportCsv);
        reportDownloadJson.addEventListener('click', exportJson);
    })();
    </script>
    <?php endif; ?>
    <?php if ($currentTab === 'catalogue'): ?>
    <script>
    (function() {
        var api = 'api/order-types.php';
        var addBtn = document.getElementById('addOrderTypeBtn');
        var modal = document.getElementById('orderTypeModal');
        var form = document.getElementById('orderTypeForm');
        var titleEl = document.getElementById('orderTypeModalTitle');
        var cancelBtn = document.getElementById('orderTypeModalCancel');
        var tbody = document.getElementById('orderTypesTableBody');
        function openModal(editRow) {
            document.getElementById('ot_id').value = editRow ? editRow.id : '';
            document.getElementById('ot_name').value = editRow ? editRow.name : '';
            document.getElementById('ot_description').value = editRow ? (editRow.description || '') : '';
            var st = document.getElementById('ot_sample_type');
            if (st) st.value = (editRow && (editRow.sample_type === 'liquid' || editRow.sample_type === 'ore')) ? editRow.sample_type : 'ore';
            document.getElementById('ot_cost').value = editRow ? editRow.cost : '0';
            document.getElementById('ot_active').checked = editRow ? !!editRow.is_active : true;
            document.getElementById('ot_activeWrap').style.display = editRow ? 'block' : 'none';
            titleEl.textContent = editRow ? 'Edit Order Type' : 'Add Order Type';
            modal.setAttribute('aria-hidden', 'false');
        }
        function closeModal() { modal.setAttribute('aria-hidden', 'true'); }
        addBtn.addEventListener('click', function() { openModal(null); });
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        tbody.addEventListener('click', function(e) {
            var editBtn = e.target.closest('.btn-edit-type');
            var delBtn = e.target.closest('.btn-delete-type');
            if (editBtn) {
                var id = parseInt(editBtn.getAttribute('data-id'), 10);
                fetch(api + '?id=' + id).then(function(r) { return r.json(); }).then(function(res) {
                    if (res.success && res.data) openModal(res.data);
                });
            }
            if (delBtn) {
                if (!confirm('Delete this order type?')) return;
                var id = parseInt(delBtn.getAttribute('data-id'), 10);
                fetch(api, { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id }) })
                    .then(function(r) { return r.json(); })
                    .then(function(res) { if (res.success) window.location.reload(); else alert(res.error); });
            }
        });
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var id = document.getElementById('ot_id').value;
            var payload = {
                name: document.getElementById('ot_name').value.trim(),
                description: document.getElementById('ot_description').value.trim(),
                sample_type: (document.getElementById('ot_sample_type') || {}).value || 'ore',
                cost: parseFloat(document.getElementById('ot_cost').value) || 0,
                is_active: document.getElementById('ot_active').checked
            };
            var req = { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) };
            if (id) {
                req.method = 'PUT';
                payload.id = parseInt(id, 10);
                req.body = JSON.stringify(payload);
            }
            fetch(api, req).then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) window.location.reload();
                    else alert(res.error || 'Failed');
                }).catch(function() { alert('Request failed'); });
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>