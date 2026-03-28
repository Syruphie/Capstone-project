<?php
require_once 'config/database.php';
require_once 'src/classes/Frontend/bootstrap.php';

$user = new FrontendUser();

if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';
$orderTypeModel = new FrontendOrderType();
$orderTypes = $orderTypeModel->getAll(true); // active only

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $priority = htmlspecialchars($_POST['priority'] ?? 'standard');
    $orderTypeId = isset($_POST['order_type_id']) ? (int) $_POST['order_type_id'] : 0;
    $compoundName = htmlspecialchars(trim($_POST['compound_name'] ?? ''));
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unit = htmlspecialchars(trim($_POST['unit'] ?? ''));

    if (empty($orderTypes)) {
        $error = 'No analysis types are currently available. Please contact support.';
    } elseif (!$orderTypeId || empty($compoundName) || $quantity <= 0 || empty($unit)) {
        $error = 'Please fill in all required fields and select an analysis type from the catalogue.';
    } else {
        try {
            $order = new FrontendOrder();
            $sample = new FrontendSample();

            $orderId = $order->createOrder($userId, $priority);

            if ($orderId) {
                $sampleId = $sample->addSample($orderId, $orderTypeId, $compoundName, $quantity, $unit);

                if ($sampleId) {
                    $success = 'Order submitted successfully! Order ID: ' . $orderId;
                } else {
                    $error = 'Failed to add sample to order. Please ensure the selected analysis type is still available.';
                }
            } else {
                $error = 'Failed to create order';
            }
        } catch (Throwable $e) {
            $error = 'Unable to submit order: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .order-form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .order-form-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .order-form-box h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .order-form-box .subtitle {
            color: #666;
            margin-bottom: 25px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 500px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="order-form-container">
        <div class="order-form-box">
            <h1>Submit New Order</h1>
            <p class="subtitle">Request chemical compound testing</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <br><a href="dashboard.php">Return to Dashboard</a>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="priority">Priority Level *</label>
                    <select id="priority" name="priority" required>
                        <option value="standard" <?php echo ($priority ?? '') === 'standard' ? 'selected' : ''; ?>>Standard (Regular Queue)</option>
                        <option value="priority" <?php echo ($priority ?? '') === 'priority' ? 'selected' : ''; ?>>Priority (Night Shift - Additional Fee)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="order_type_id">Analysis Type (from catalogue) *</label>
                    <select id="order_type_id" name="order_type_id" required>
                        <option value="">Select analysis type...</option>
                        <?php foreach ($orderTypes as $ot): ?>
                        <option value="<?php echo (int) $ot['id']; ?>" data-cost="<?php echo number_format($ot['cost'], 2); ?>" <?php echo (isset($_POST['order_type_id']) && (int)$_POST['order_type_id'] === (int)$ot['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ot['name']); ?> — $<?php echo number_format($ot['cost'], 2); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($orderTypes)): ?>
                    <p class="form-help" style="color:#856404;font-size:13px;margin-top:6px;">No analysis types available. An administrator must add types in the Order Catalogue.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="compound_name">Compound Name *</label>
                    <input 
    type="text" 
    id="compound_name" 
    name="compound_name" 
    required 
    placeholder="e.g., Iron Oxide, Sulfuric Acid"
    value="<?php echo htmlspecialchars($_POST['compound_name'] ?? ''); ?>"
>

                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                       <input 
    type="number" 
    id="quantity" 
    name="quantity" 
    required 
    min="0.01" 
    step="0.01"
    placeholder="Amount"
    value="<?php echo htmlspecialchars($_POST['quantity'] ?? ''); ?>"
>

                    </div>

                    <div class="form-group">
                        <label for="unit">Unit *</label>
                        <select id="unit" name="unit" required>
                           <option value="g"  <?php echo ($unit ?? '') === 'g'  ? 'selected' : ''; ?>>Grams (g)</option>
<option value="kg" <?php echo ($unit ?? '') === 'kg' ? 'selected' : ''; ?>>Kilograms (kg)</option>
<option value="mL" <?php echo ($unit ?? '') === 'mL' ? 'selected' : ''; ?>>Milliliters (mL)</option>
<option value="L"  <?php echo ($unit ?? '') === 'L'  ? 'selected' : ''; ?>>Liters (L)</option>

                        </select>
                    </div>
                </div>

                <button type="submit" name="submit_order" class="btn btn-primary">Submit Order</button>
            </form>

            <div class="login-footer">
                <p><a href="dashboard.php">← Back to Dashboard</a></p>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>

