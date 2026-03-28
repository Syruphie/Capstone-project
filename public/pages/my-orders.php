<?php
require_once 'config/database.php';
require_once 'src/classes/Frontend/bootstrap.php';
 
$user = new FrontendUser();
 
// Check if user is logged in and is customer
if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: login.php');
    exit;
}
 
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
 
// Initialize Order class
$order = new FrontendOrder();
 
// Get customer's orders
$orders = $order->getOrdersByCustomer($userId);
 
// Count orders by status
$statusCounts = [
    'submitted' => 0,
    'approved' => 0,
    'processing' => 0,
    'completed' => 0,
    'rejected' => 0
];
 
foreach ($orders as $o) {
    if (isset($statusCounts[$o['status']])) {
        $statusCounts[$o['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .orders-header {
            background: white;
            border-radius: 10px;
            padding: 25px 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .orders-header h1 {
            color: #333;
            margin-bottom: 5px;
        }
        .orders-header p {
            color: #666;
            margin: 0;
        }
        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .status-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .status-card .count {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .status-card .label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .status-card.pending .count { color: #ffc107; }
        .status-card.approved .count { color: #17a2b8; }
        .status-card.processing .count { color: #667eea; }
        .status-card.completed .count { color: #28a745; }
        .status-card.rejected .count { color: #dc3545; }
        .orders-table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .orders-table-container h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-submitted { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #e7e3ff; color: #5a4fcf; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-orders h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .empty-orders p {
            margin-bottom: 20px;
        }
        .new-order-btn {
            display: inline-block;
            margin-top: 15px;
        }
        .orders-table-footer {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
 
    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <p>View and track all your submitted orders</p>
        </div>
 
        <div class="status-cards">
            <div class="status-card pending">
                <div class="count"><?php echo $statusCounts['submitted']; ?></div>
                <div class="label">Pending</div>
            </div>
            <div class="status-card approved">
                <div class="count"><?php echo $statusCounts['approved']; ?></div>
                <div class="label">Approved</div>
            </div>
            <div class="status-card processing">
                <div class="count"><?php echo $statusCounts['processing']; ?></div>
                <div class="label">Processing</div>
            </div>
            <div class="status-card completed">
                <div class="count"><?php echo $statusCounts['completed']; ?></div>
                <div class="label">Completed</div>
            </div>
            <div class="status-card rejected">
                <div class="count"><?php echo $statusCounts['rejected']; ?></div>
                <div class="label">Rejected</div>
            </div>
        </div>
 
        <div class="orders-table-container">
            <h2>Order History</h2>
 
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <h3>No Orders Yet</h3>
                    <p>You haven't submitted any orders. Start by creating a new order.</p>
                    <a href="create-order.php" class="btn btn-primary new-order-btn">Create New Order</a>
                </div>
            <?php else: ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Submitted</th>
                                <th>Priority</th>
                                <th>Samples</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($o['created_at'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $o['priority']; ?>">
                                        <?php echo ucfirst($o['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo $o['sample_count']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $o['status']; ?>">
                                        <?php echo ucfirst($o['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (in_array($o['status'], ['approved', 'payment_pending', 'in_queue'], true)): ?>
                                        <a href="checkout.php?order_id=<?php echo (int) $o['id']; ?>" class="btn btn-small btn-primary">Pay Now</a>
                                    <?php elseif (in_array($o['status'], ['payment_confirmed', 'results_available', 'completed'], true)): ?>
                                        <a href="invoice.php?order_id=<?php echo (int) $o['id']; ?>" class="btn btn-small btn-secondary">View Invoice</a>
                                    <?php else: ?>
                                        <a href="#" class="btn btn-small btn-secondary">View Details</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
 
                <div class="orders-table-footer">
                    <a href="create-order.php" class="btn btn-primary">Create New Order</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
 
    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>

