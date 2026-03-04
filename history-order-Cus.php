<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
  header('Location: login.php');
  exit;
}

$customerId = $_SESSION['user_id'];

$orderObj = new Order();
$ordersAll = $orderObj->getOrderHistoryByCustomer($customerId);

/* keep only completed + rejected for "Order History" */
$orders = [];
$completedCount = 0;
$rejectedCount = 0;

foreach ($ordersAll as $o) {
  $status = strtolower($o['status'] ?? '');
  if ($status === 'completed')
    $completedCount++;
  if ($status === 'rejected')
    $rejectedCount++;

  if (in_array($status, ['completed', 'rejected'])) {
    $orders[] = $o;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order History -
    <?php echo APP_NAME; ?>
  </title>

  <link rel="stylesheet" href="css/style.css" />

  <!-- ✅ ALL-IN-ONE CSS (Dashboard containers + smaller stat cards + bottom button) -->
  <style>
    :root {
      --text: #0f172a;
      --muted: #64748b;
      --border: rgba(15, 23, 42, .10);
    }

    /* MAIN WRAPPER (same as dashboard) */
    .dashboard-container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 40px 20px 60px;
    }

    /* HEADER CARD (same as dashboard welcome-section) */
    .welcome-section {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 6px;
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

    /* GRID (same as dashboard) */
    .dashboard-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 26px;
    }

    /* CARDS (same as dashboard) */
    .dashboard-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 6px;
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

    /* ✅ smaller stat cards */
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

    /* STATS */
    .card-stats .stat {
      font-size: 28px;
      font-weight: 900;
      color: #5b4ae6;
      display: inline-block;
      margin-bottom: 0;
    }

    .stat.completed {
      color: #16a34a;
    }

    .stat.rejected {
      color: #dc2626;
    }

    /* FULL WIDTH (same as dashboard) */
    .dashboard-card.full-width {
      grid-column: 1 / -1;
      min-height: auto;
    }

    /* TABLE (same as dashboard) */
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

    /* BUTTONS */
    .btn {
      border-radius: 16px;
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

    /* Status pill */
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

    .pill-completed {
      background: rgba(22, 163, 74, .12);
      color: #166534;
    }

    .pill-rejected {
      background: rgba(220, 38, 38, .12);
      color: #991b1b;
    }

    .empty-state {
      text-align: center;
      padding: 18px !important;
      font-weight: 900;
      color: var(--muted);
    }

    /* ✅ Create button row at bottom */
    .history-actions {
      padding-top: 18px;
      display: flex;
      justify-content: flex-end;
    }

    /* responsive */
    @media (max-width: 900px) {
      .dashboard-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 600px) {
      .history-actions {
        justify-content: center;
      }

      .history-actions .btn {
        width: 100%;
        max-width: 360px;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="dashboard-container">

    <div class="welcome-section">
      <h1>Order History</h1>
      <p>View your completed and rejected orders</p>
    </div>

    <div class="dashboard-grid">

      <div class="dashboard-card stat-card">
        <div>
          <h2>Completed Orders</h2>
          <p>Total orders that finished successfully.</p>
        </div>
        <div class="card-stats">
          <div class="stat completed">
            <?php echo (int) $completedCount; ?> Completed
          </div>
        </div>
      </div>

      <div class="dashboard-card stat-card">
        <div>
          <h2>Rejected Orders</h2>
          <p>Total orders that were rejected by the lab.</p>
        </div>
        <div class="card-stats">
          <div class="stat rejected">
            <?php echo (int) $rejectedCount; ?> Rejected
          </div>
        </div>
      </div>

      <div class="dashboard-card full-width">
        <h2 style="margin-bottom:14px;">Past Orders</h2>

        <table class="dashboard-table">
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
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="6" class="empty-state">No completed or rejected orders found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $o): ?>
                <?php
                $id = (int) ($o['id'] ?? 0);
                $orderNumber = $o['order_number'] ?? ('ORD-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT));
                $priority = strtoupper($o['priority'] ?? 'STANDARD');
                $samples = (int) ($o['sample_count'] ?? 0);
                $status = strtolower($o['status'] ?? '');
                $pill = ($status === 'completed') ? 'pill-completed' : 'pill-rejected';
                ?>
                <tr>
                  <td>
                    <?php echo htmlspecialchars($orderNumber); ?>
                  </td>
                  <td>
                    <?php echo !empty($o['created_at']) ? date('M d, Y H:i', strtotime($o['created_at'])) : '-'; ?>
                  </td>
                  <td>
                    <?php echo htmlspecialchars($priority); ?>
                  </td>
                  <td>
                    <?php echo $samples; ?>
                  </td>
                  <td><span class="status-pill <?php echo $pill; ?>">
                      <?php echo htmlspecialchars($status); ?>
                    </span></td>
                  <td>
                    <a class="btn btn-primary" href="order-details-cus.php?id=<?php echo $id; ?>">View Details</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <div class="history-actions">
          <a class="btn btn-primary" href="create-order.php">Create New Order</a>
        </div>
      </div>

    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
  <script src="js/main.js"></script>
</body>

</html>