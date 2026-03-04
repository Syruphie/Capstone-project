<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

if (!$user->isLoggedIn()) {
  header("Location: login.php");
  exit;
}

$orderObj = new Order();

/* Get all orders */
$ordersAll = $orderObj->getOrderHistoryForAdmin();

/* Count orders */
$completedCount = 0;
$rejectedCount = 0;
$approvedCount = 0;

foreach ($ordersAll as $o) {

  $status = strtolower($o['status']);

  if ($status == 'completed')
    $completedCount++;
  if ($status == 'rejected')
    $rejectedCount++;
  if ($status == 'approved')
    $approvedCount++;

}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Admin Order History</title>

  <link rel="stylesheet" href="css/style.css">

  <style>
    .dashboard-container {
      max-width: 1100px;
      margin: auto;
      padding: 40px 20px 60px;
    }

    .welcome-section {
      background: #fff;
      padding: 35px;
      border-radius: 10px;
      margin-bottom: 35px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    }

    .welcome-section h1 {
      font-size: 36px;
      margin-bottom: 5px;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 25px;
      margin-bottom: 30px;
    }

    .dashboard-card {
      background: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
      transition: 0.2s;
    }

    .dashboard-card:hover {
      transform: translateY(-4px);
    }

    .dashboard-card h2 {
      margin-bottom: 5px;
    }

    .dashboard-card p {
      color: #777;
      margin-bottom: 10px;
    }

    .stat {
      font-size: 34px;
      font-weight: 900;
      margin-top: 10px;
    }

    .completed {
      color: #16a34a;
    }

    .rejected {
      color: #dc2626;
    }

    .approved {
      color: #2563eb;
    }

    .dashboard-card.full-width {
      grid-column: 1/-1;
      margin-top: 10px;
    }

    .dashboard-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    .dashboard-table th {
      text-align: left;
      padding: 14px;
      background: #f7f7f7;
      border-bottom: 1px solid #ddd;
    }

    .dashboard-table td {
      padding: 14px;
      border-bottom: 1px solid #eee;
    }

    .status-pill {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }

    .pill-completed {
      background: #e7f8ec;
      color: #0f7a32;
    }

    .pill-rejected {
      background: #fde8e8;
      color: #a61b1b;
    }

    .pill-approved {
      background: #e8f0ff;
      color: #1e40af;
    }
  </style>

</head>

<body>

  <?php include 'includes/header.php'; ?>

  <div class="dashboard-container">

    <div class="welcome-section">
      <h1>Order History</h1>
      <p>View all orders</p>
    </div>

    <div class="dashboard-grid">

      <div class="dashboard-card">
        <h2>Completed Orders</h2>
        <p>Total finished orders</p>
        <div class="stat completed">
          <?php echo $completedCount; ?>
        </div>
      </div>

      <div class="dashboard-card">
        <h2>Rejected Orders</h2>
        <p>Total rejected orders</p>
        <div class="stat rejected">
          <?php echo $rejectedCount; ?>
        </div>
      </div>

      <div class="dashboard-card">
        <h2>Approved Orders</h2>
        <p>Total approved orders</p>
        <div class="stat approved">
          <?php echo $approvedCount; ?>
        </div>
      </div>

      <div class="dashboard-card full-width">

        <h2>All Past Orders</h2>

        <table class="dashboard-table">

          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Company</th>
              <th>Submitted</th>
              <th>Priority</th>
              <th>Samples</th>
              <th>Status</th>
            </tr>
          </thead>

          <tbody>

            <?php foreach ($ordersAll as $o): ?>

              <?php
              $status = strtolower($o['status']);

              $pill = '';

              if ($status == 'completed')
                $pill = 'pill-completed';
              if ($status == 'rejected')
                $pill = 'pill-rejected';
              if ($status == 'approved')
                $pill = 'pill-approved';
              ?>

              <tr>

                <td>
                  <?php echo $o['order_number']; ?>
                </td>

                <td>
                  <?php echo $o['customer_name']; ?>
                </td>

                <td>
                  <?php echo $o['company_name']; ?>
                </td>

                <td>
                  <?php echo date("M d Y H:i", strtotime($o['created_at'])); ?>
                </td>

                <td>
                  <?php echo strtoupper($o['priority']); ?>
                </td>

                <td>
                  <?php echo $o['sample_count']; ?>
                </td>

                <td>
                  <span class="status-pill <?php echo $pill ?>">
                    <?php echo ucfirst($status); ?>
                  </span>
                </td>

              </tr>

            <?php endforeach; ?>

          </tbody>

        </table>

      </div>

    </div>

  </div>

  <?php include 'includes/footer.php'; ?>

</body>

</html>