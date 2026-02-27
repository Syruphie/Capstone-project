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
$orders = $orderObj->getOrderHistoryByCustomer($customerId);


$completedCount = 0;
$rejectedCount = 0;

foreach ($orders as $o) {
  if ($o['status'] === 'completed')
    $completedCount++;
  if ($o['status'] === 'rejected')
    $rejectedCount++;
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
  <link rel="stylesheet" href="css/admin.css" />

  <style>
    <style>:root {
      --oh-text: #0f172a;
      --oh-muted: #64748b;
      --oh-border: rgba(15, 23, 42, .10);

      --oh-primary: #5b4ae6;
      --oh-primary2: #7c3aed;

      --r1: 14px;
      --r2: 18px;
      --r3: 22px;

      --sh1: 0 1px 2px rgba(15, 23, 42, .06);
      --sh2: 0 10px 30px rgba(15, 23, 42, .12);
      --sh3: 0 18px 60px rgba(15, 23, 42, .14);
    }

    /* Page container */
    .orders-container {
      max-width: 1120px;
      margin: 0 auto;
      padding: 32px 16px 50px;
    }

    /* Top header card */
    .orders-header {
      background: rgba(255, 255, 255, .92);
      border: 1px solid rgba(255, 255, 255, .65);
      border-radius: var(--r3);
      padding: 28px;
      margin-bottom: 18px;
      box-shadow: var(--sh3);
      backdrop-filter: blur(8px);
    }

    .orders-header h1 {
      margin: 0;
      font-size: 44px;
      line-height: 1.05;
      letter-spacing: -0.03em;
      font-weight: 700;
      color: var(--oh-text);
    }

    .orders-header p {
      margin: 10px 0 0;
      color: var(--oh-muted);
      font-size: 16px;
      line-height: 1.6;
    }

    /* Stats cards */
    .status-cards {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      margin-bottom: 18px;
    }

    .status-card {
      background: rgba(255, 255, 255, .92);
      border: 1px solid rgba(255, 255, 255, .65);
      border-radius: var(--r3);
      padding: 22px;
      text-align: center;
      box-shadow: var(--sh2);
      backdrop-filter: blur(8px);
    }

    .status-card .count {
      font-size: 52px;
      font-weight: 900;
      margin-bottom: 8px;
      line-height: 1;
      letter-spacing: -0.02em;
    }

    .status-card .label {
      color: var(--oh-muted);
      font-size: 15px;
      font-weight: 700;
    }

    .status-card.completed .count {
      color: #16a34a;
    }

    .status-card.rejected .count {
      color: #dc2626;
    }

    /* Main section card */
    .section-card {
      background: rgba(255, 255, 255, .92);
      border: 1px solid rgba(255, 255, 255, .65);
      border-radius: var(--r3);
      padding: 0;
      box-shadow: var(--sh3);
      overflow: hidden;
      backdrop-filter: blur(8px);
    }

    .section-title {
      font-size: 26px;
      font-weight: 900;
      letter-spacing: -0.02em;
      color: var(--oh-text);
      margin: 0;
      padding: 18px 22px;
      border-bottom: 1px solid rgba(15, 23, 42, .08);
    }

    /* Bigger table */
    .admin-table-container {
      padding: 0;
      overflow-x: auto;
    }

    .admin-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      font-size: 15px;
    }

    .admin-table thead th {
      text-align: left;
      padding: 14px 16px;
      background: rgba(15, 23, 42, .02);
      color: var(--oh-muted);
      font-weight: 900;
      border-bottom: 1px solid rgba(15, 23, 42, .10);
      white-space: nowrap;
    }

    .admin-table tbody td {
      padding: 16px 16px;
      border-bottom: 1px solid rgba(15, 23, 42, .08);
      color: var(--oh-text);
      vertical-align: middle;
    }

    .admin-table tbody tr:hover {
      background: rgba(91, 74, 230, .05);
    }

    .empty-state {
      text-align: center;
      padding: 26px 16px !important;
      color: var(--oh-muted);
      font-weight: 700;
    }

    /* Status pills */
    .status-pill {
      display: inline-flex;
      align-items: center;
      padding: 8px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 900;
      letter-spacing: .04em;
      text-transform: uppercase;
      border: 1px solid rgba(15, 23, 42, .10);
    }

    .pill-rejected {
      background: rgba(220, 38, 38, .10);
      border-color: rgba(220, 38, 38, .18);
      color: #991b1b;
    }

    .pill-completed {
      background: rgba(22, 163, 74, .10);
      border-color: rgba(22, 163, 74, .18);
      color: #166534;
    }

    .pill-approved {
      background: rgba(14, 165, 233, .10);
      border-color: rgba(14, 165, 233, .18);
      color: #075985;
    }

    .pill-processing {
      background: rgba(91, 74, 230, .10);
      border-color: rgba(91, 74, 230, .18);
      color: #4338ca;
    }

    /* Buttons */
    .btn.btn-small.btn-primary {
      border-radius: 14px;
      padding: 10px 14px;
      font-weight: 900;
    }

    /* CTA */
    .cta-wrap {
      padding: 18px 22px 22px;
    }

    .cta-full {
      width: 100%;
      display: block;
      text-align: center;
      padding: 16px 18px;
      border-radius: 16px;
      font-weight: 900;
      font-size: 16px;
      color: #fff !important;
      background: linear-gradient(90deg, #5b4ae6, #7c3aed) !important;
      box-shadow: 0 14px 26px rgba(91, 74, 230, .25);
      text-decoration: none !important;
    }

    .cta-full:hover {
      filter: brightness(1.05);
    }

    /* Responsive */
    @media (max-width:900px) {
      .status-cards {
        grid-template-columns: 1fr;
      }

      .orders-header h1 {
        font-size: 34px;
      }
    }
  </style>
</head>

<body>

  <?php include 'includes/header.php'; ?>

  <div class="orders-container">

    <div class="orders-header">
      <h1>Order History</h1>
      <p>View your completed and rejected orders</p>
    </div>

    <div class="status-cards">
      <div class="status-card completed">
        <div class="count">
          <?php echo $completedCount; ?>
        </div>
        <div class="label">Completed</div>
      </div>
      <div class="status-card rejected">
        <div class="count">
          <?php echo $rejectedCount; ?>
        </div>
        <div class="label">Rejected</div>
      </div>
    </div>

    <div class="section-card">
      <div class="section-title">Past Orders</div>

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
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="6" class="empty-state">No past orders found</td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $o): ?>
                <?php
                $status = $o['status'];
                $pillClass = 'pill-processing';
                if ($status === 'rejected')
                  $pillClass = 'pill-rejected';
                if ($status === 'completed')
                  $pillClass = 'pill-completed';
                if ($status === 'approved')
                  $pillClass = 'pill-approved';
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
                    <?php echo (int) ($o['sample_count'] ?? 0); ?>
                  </td>
                  <td>
                    <span class="status-pill <?php echo $pillClass; ?>">
                      <?php echo htmlspecialchars($status); ?>
                    </span>
                  </td>
                  <td>
                    <a href="order-details-cus.php?id=<?php echo (int) $o['id']; ?>" class="btn btn-small btn-primary">
                      View Details
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="cta-wrap">
        <a href="create-order.php" class="btn btn-primary cta-full">Create New Order</a>
      </div>

    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
  <script src="js/main.js"></script>

</body>

</html>