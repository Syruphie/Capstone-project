<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
  header('Location: login.php');
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: history-order-cus.php');
  exit;
}

$orderId = (int) $_GET['id'];
$customerId = $_SESSION['user_id'];

$orderObj = new Order();
$order = $orderObj->getOrderByIdForCustomer($orderId, $customerId);

if (!$order) {
  header('Location: history-order-cus.php');
  exit;
}

$samples = $orderObj->getSamplesByOrderId($orderId);

function formatStatusLabel($s)
{
  return strtoupper(str_replace('_', ' ', $s));
}

function statusPillClass($s)
{
  $s = strtolower($s);
  if ($s === 'rejected')
    return 'pill-rejected';
  if ($s === 'completed')
    return 'pill-completed';
  if ($s === 'approved')
    return 'pill-approved';
  return 'pill-processing';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Details -
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
      /* SHARP */
      padding: 32px 32px;
      margin-bottom: 28px;
      box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
    }

    .orders-header h1 {
      margin: 0;
      font-size: 46px;
      font-weight: 900;
      letter-spacing: -1px;
      color: var(--text);
    }

    .orders-header p {
      margin-top: 10px;
      color: var(--muted);
      font-size: 16px;
    }

    /* SHARP OUTER SECTION */
    .section-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 6px;
      /* SHARP */
      margin-bottom: 28px;
      box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
      overflow: hidden;
    }

    .section-title {
      padding: 20px 28px;
      font-size: 26px;
      font-weight: 900;
      border-bottom: 1px solid var(--border);
    }

    /* TOP STATUS AREA */
    .top-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 22px 28px;
      border-bottom: 1px solid var(--border);
      flex-wrap: wrap;
      gap: 16px;
    }

    /* STATUS PILL (ROUNDED INSIDE) */
    .status-pill {
      padding: 8px 16px;
      border-radius: 999px;
      /* ROUNDED */
      font-size: 12px;
      font-weight: 900;
      letter-spacing: .5px;
      text-transform: uppercase;
      border: 1px solid rgba(0, 0, 0, .1);
    }

    .pill-rejected {
      background: rgba(220, 38, 38, .12);
      color: #991b1b;
    }

    .pill-completed {
      background: rgba(22, 163, 74, .12);
      color: #166534;
    }

    .pill-approved {
      background: rgba(14, 165, 233, .12);
      color: #075985;
    }

    .pill-processing {
      background: rgba(91, 74, 230, .12);
      color: #4338ca;
    }

    /* INNER INFO GRID (ROUNDED BOXES) */
    .info-grid {
      padding: 28px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 22px;
    }

    .info-box {
      background: rgba(15, 23, 42, .03);
      border: 1px solid rgba(15, 23, 42, .08);
      border-radius: 20px;
      /* ROUNDED INSIDE */
      padding: 18px 18px;
    }

    .label {
      font-size: 12px;
      font-weight: 900;
      letter-spacing: .5px;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 6px;
    }

    .value {
      font-size: 16px;
      font-weight: 800;
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
    }

    .admin-table td {
      padding: 18px 16px;
      border-bottom: 1px solid var(--border);
    }

    .admin-table tr:hover {
      background: rgba(91, 74, 230, .05);
    }

    /* BUTTONS (ROUNDED) */
    .cta-btn {
      padding: 14px 20px;
      border-radius: 16px;
      font-weight: 900;
      font-size: 14px;
      background: linear-gradient(90deg, #5b4ae6, #7c3aed);
      color: #fff;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 12px 24px rgba(91, 74, 230, .25);
    }

    .cta-btn:hover {
      filter: brightness(1.05);
    }

    /* RESPONSIVE */
    @media(max-width:900px) {
      .info-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="orders-container">

    <div class="orders-header">
      <h1>Order Details</h1>
      <p>Order #: <strong>
          <?php echo htmlspecialchars($order['order_number']); ?>
        </strong></p>
    </div>

    <div class="section-card">
      <div class="section-title">Order Information</div>

      <div class="top-row">
        <div>
          <span class="status-pill <?php echo statusPillClass($order['status']); ?>">
            <?php echo formatStatusLabel($order['status']); ?>
          </span>
        </div>

        <div>
          <a href="history-order-cus.php" class="cta-btn">Back to History</a>
          <a href="create-order.php" class="cta-btn">Create New Order</a>
        </div>
      </div>

      <div class="info-grid">
        <div class="info-box">
          <div class="label">Submitted</div>
          <div class="value">
            <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
          </div>
        </div>

        <div class="info-box">
          <div class="label">Priority</div>
          <div class="value">
            <?php echo strtoupper($order['priority']); ?>
          </div>
        </div>

        <div class="info-box">
          <div class="label">Last Updated</div>
          <div class="value">
            <?php
            $updated = $order['updated_at'] ?? $order['created_at'];
            echo date('M d, Y H:i', strtotime($updated));
            ?>
          </div>
        </div>

        <div class="info-box">
          <div class="label">Samples</div>
          <div class="value">
            <?php echo count($samples); ?>
          </div>
        </div>
      </div>
    </div>

    <div class="section-card">
      <div class="section-title">Samples</div>

      <div class="admin-table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Sample #</th>
              <th>Name</th>
              <th>Type</th>
              <th>Submitted</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($samples)): ?>
              <tr>
                <td colspan="4">No samples found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($samples as $s): ?>
                <tr>
                  <td>
                    <?php echo $s['id']; ?>
                  </td>
                  <td>
                    <?php echo htmlspecialchars($s['compound_name'] ?? ''); ?>
                  </td>
                  <td>
                    <?php echo htmlspecialchars($s['sample_type'] ?? ''); ?>
                  </td>
                  <td>
                    <?php echo date('M d, Y H:i', strtotime($s['created_at'])); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <?php include 'includes/footer.php'; ?>
</body>

</html>