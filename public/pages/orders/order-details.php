<?php
require_once __DIR__ . '/../bootstrap_paths.php';

$user = new FrontendUser();
if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}

$customerId = (int) ($_SESSION['user_id'] ?? 0);
$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : (int) ($_POST['order_id'] ?? 0);

if ($orderId <= 0) {
    http_response_code(400);
    echo 'Invalid order.';
    exit;
}

$orderModel = new FrontendOrder();
$sampleModel = new FrontendSample();

$alertError = '';
$alertSuccess = '';

$cancellableStatuses = ['submitted', 'pending_approval'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    if ($orderModel->cancelOrderByCustomer($orderId, $customerId)) {
        $alertSuccess = 'Order cancelled successfully.';
    } else {
        $alertError = 'Unable to cancel this order. It may already be approved or no longer cancellable.';
    }
}

$order = $orderModel->getOrderByIdForCustomer($orderId, $customerId);
if (!$order) {
    http_response_code(403);
    echo 'Order not found or access denied.';
    exit;
}

$samples = $sampleModel->getSamplesByOrder($orderId);
$canCancel = in_array((string) $order['status'], $cancellableStatuses, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .order-details-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        .order-details-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 24px;
            margin-bottom: 20px;
        }
        .order-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        .order-meta-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            background: #f8fafc;
        }
        .order-meta-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
            display: block;
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
        .status-processing, .status-pending_approval, .status-payment_pending,
        .status-payment_confirmed, .status-in_queue, .status-preparation_in_progress,
        .status-testing_in_progress { background: #e7e3ff; color: #5a4fcf; }
        .status-completed, .status-results_available { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .actions-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }
    </style>
</head>
<body>
    <?php include PAGE_PARTIALS . '/header.php'; ?>

    <div class="order-details-container">
        <div class="order-details-card">
            <h1>Order Details</h1>
            <p>Review the latest status and sample information for your order.</p>

            <?php if ($alertSuccess): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($alertSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($alertError): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($alertError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="order-meta-grid">
                <div class="order-meta-item">
                    <span class="order-meta-label">Order Number</span>
                    <strong><?php echo htmlspecialchars((string) $order['order_number'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="order-meta-item">
                    <span class="order-meta-label">Status</span>
                    <span class="status-badge status-<?php echo htmlspecialchars((string) $order['status'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars(ucfirst((string) $order['status']), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div class="order-meta-item">
                    <span class="order-meta-label">Priority</span>
                    <strong><?php echo htmlspecialchars(ucfirst((string) $order['priority']), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="order-meta-item">
                    <span class="order-meta-label">Created</span>
                    <strong><?php echo htmlspecialchars(date('M d, Y H:i', strtotime((string) $order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="order-meta-item">
                    <span class="order-meta-label">Updated</span>
                    <strong><?php echo htmlspecialchars(date('M d, Y H:i', strtotime((string) $order['updated_at'])), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="order-meta-item">
                    <span class="order-meta-label">Samples</span>
                    <strong><?php echo (int) ($order['sample_count'] ?? 0); ?></strong>
                </div>
            </div>

            <div class="actions-row">
                <a href="<?php echo htmlspecialchars(app_path('orders/my-orders.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">Back to My Orders</a>
                <?php if ($canCancel): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo (int) $orderId; ?>">
                        <button type="submit" name="cancel_order" class="btn btn-danger">Cancel Order</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-table-container">
            <h2>Samples</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Sample #</th>
                        <th>Compound</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($samples)): ?>
                        <tr>
                            <td colspan="5" class="empty-state">No samples found for this order.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($samples as $sample): ?>
                            <tr>
                                <td><?php echo (int) $sample['id']; ?></td>
                                <td><?php echo htmlspecialchars((string) ($sample['compound_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) ($sample['quantity'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>$<?php echo number_format((float) ($sample['unit_cost'] ?? 0), 2); ?></td>
                                <td><?php echo htmlspecialchars((string) ($sample['status'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include PAGE_PARTIALS . '/footer.php'; ?>
</body>
</html>
