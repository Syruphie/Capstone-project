<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

// Check if user is logged in and is customer
if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Customer';

// Initialize Order class
$order = new Order();

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
    <title>My Orders -
        <?php echo APP_NAME; ?>
    </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">

    <style>
        :root {
            --text: #0f172a;
            --muted: #64748b;
            --border: rgba(15, 23, 42, .10);
        }

        /* PAGE WRAPPER */
        .orders-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        /* SHARP OUTER HEADER */
        .orders-header {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 32px;
            margin-bottom: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
        }

        .orders-header h1 {
            margin: 0 0 8px;
            font-size: 46px;
            font-weight: 700;
            letter-spacing: -1px;
            color: var(--text);
        }

        .orders-header p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
        }

        /* STATUS CARDS - SAME SIZE */
        .status-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .status-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 18px 14px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .status-card .count {
            font-size: 34px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 8px;
        }

        .status-card .label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .3px;
        }

        .status-card.pending .count {
            color: #ca8a04;
        }

        .status-card.approved .count {
            color: #0284c7;
        }

        .status-card.processing .count {
            color: #5b4ae6;
        }

        .status-card.completed .count {
            color: #16a34a;
        }

        .status-card.rejected .count {
            color: #dc2626;
        }

        /* MAIN TABLE CARD (SHARP OUTSIDE) */
        .section-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
            overflow: hidden;
        }

        .section-title {
            padding: 20px 28px;
            font-size: 26px;
            font-weight: 900;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        /* TABLE */
        .admin-table-container {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        .admin-table th {
            text-align: left;
            padding: 16px;
            font-weight: 900;
            border-bottom: 1px solid var(--border);
            color: var(--muted);
            background: #fafafa;
            white-space: nowrap;
        }

        .admin-table td {
            padding: 18px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .admin-table tr:hover {
            background: rgba(91, 74, 230, .05);
        }

        /* STATUS PILL (ROUNDED INSIDE) */
        .status-pill {
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

        .pill-submitted {
            background: rgba(202, 138, 4, .12);
            color: #854d0e;
        }

        .pill-approved {
            background: rgba(14, 165, 233, .12);
            color: #075985;
        }

        .pill-processing {
            background: rgba(91, 74, 230, .12);
            color: #4338ca;
        }

        .pill-completed {
            background: rgba(22, 163, 74, .12);
            color: #166534;
        }

        .pill-rejected {
            background: rgba(220, 38, 38, .12);
            color: #991b1b;
        }

        /* BUTTONS (ROUNDED) */
        .btn {
            border-radius: 16px;
            font-weight: 900;
            padding: 12px 16px;
        }

        .btn.btn-small {
            padding: 10px 14px;
            border-radius: 14px;
        }

        /* EMPTY STATE */
        .empty-orders {
            padding: 50px 28px;
            text-align: center;
            color: var(--muted);
        }

        .empty-orders h3 {
            margin: 0 0 8px;
            color: var(--text);
            font-size: 22px;
            font-weight: 900;
        }

        .empty-orders p {
            margin: 0 0 18px;
        }

        /* CTA AREA */
        .cta-row {
            padding: 18px 22px 22px;
            display: flex;
            justify-content: flex-end;
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .status-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .status-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="orders-container">
        <div class="orders-header">
            <h1>My Orders</h1>
            <p>View and track all your submitted orders.</p>
        </div>

        <div class="status-cards">
            <div class="status-card pending">
                <div class="count">
                    <?php echo $statusCounts['submitted']; ?>
                </div>
                <div class="label">Pending</div>
            </div>
            <div class="status-card approved">
                <div class="count">
                    <?php echo $statusCounts['approved']; ?>
                </div>
                <div class="label">Approved</div>
            </div>
            <div class="status-card processing">
                <div class="count">
                    <?php echo $statusCounts['processing']; ?>
                </div>
                <div class="label">Processing</div>
            </div>
            <div class="status-card completed">
                <div class="count">
                    <?php echo $statusCounts['completed']; ?>
                </div>
                <div class="label">Completed</div>
            </div>
            <div class="status-card rejected">
                <div class="count">
                    <?php echo $statusCounts['rejected']; ?>
                </div>
                <div class="label">Rejected</div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-title">Order History</div>

            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <h3>No Orders Yet</h3>
                    <p>You haven't submitted any orders. Start by creating a new order.</p>
                    <a href="create-order.php" class="btn btn-primary">Create New Order</a>
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
                                <?php
                                $st = strtolower($o['status']);
                                $pill = 'pill-processing';
                                if ($st === 'submitted')
                                    $pill = 'pill-submitted';
                                if ($st === 'approved')
                                    $pill = 'pill-approved';
                                if ($st === 'processing')
                                    $pill = 'pill-processing';
                                if ($st === 'completed')
                                    $pill = 'pill-completed';
                                if ($st === 'rejected')
                                    $pill = 'pill-rejected';
                                ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($o['order_number']); ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y H:i', strtotime($o['created_at'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($o['priority']); ?>">
                                            <?php echo strtoupper(htmlspecialchars($o['priority'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo (int) $o['sample_count']; ?>
                                    </td>
                                    <td>
                                        <span class="status-pill <?php echo $pill; ?>">
                                            <?php echo htmlspecialchars($st); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- customer details page -->
                                        <a href="order-details-cus.php?id=<?php echo (int) $o['id']; ?>"
                                            class="btn btn-small btn-primary">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cta-row">
                    <a href="create-order.php" class="btn btn-primary">Create New Order</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>

</html>