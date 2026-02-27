<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();
if (!$user->isLoggedIn() || $user->getRole() !== 'administrator') {
  header('Location: login.php');
  exit;
}

$orderObj = new Order();
$orders = $orderObj->getOrderHistoryForAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order History -
    <?php echo APP_NAME; ?>
  </title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

  <div class="orders-container">
    <div class="orders-header">
      <h1>Order History</h1>
      <p>Admin view of all orders</p>
    </div>

      <?php include 'includes/order-history-table.php'; ?>
  </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>