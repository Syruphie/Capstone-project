<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';
require_once 'classes/Sample.php';

$user = new User();

// Check if user is logged in and is customer
if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $priority = $_POST['priority'] ?? 'standard';
    $sampleType = $_POST['sample_type'] ?? '';
    $compoundName = trim($_POST['compound_name'] ?? '');
    $quantity = floatval($_POST['quantity'] ?? 0);
    $unit = trim($_POST['unit'] ?? '');

    // Basic validation
    if (empty($sampleType) || empty($compoundName) || $quantity <= 0 || empty($unit)) {
        $error = 'Please fill in all required fields';
    } else {
        $order = new Order();
        $sample = new Sample();

        // Create order
        $orderId = $order->createOrder($userId, $priority);

        if ($orderId) {
            // Add sample to order
            $sampleId = $sample->addSample($orderId, $sampleType, $compoundName, $quantity, $unit);

            if ($sampleId) {
                $success = 'Order submitted successfully! Order ID: ' . $orderId;
            } else {
                $error = 'Failed to add sample to order';
            }
        } else {
            $error = 'Failed to create order';
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
    <link rel="stylesheet" href="css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <style>
        .order-form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .order-form-box {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 30px;
            box-shadow: var(--shadow-md);
        }
        .order-form-box h1 {
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        .order-form-box .subtitle {
            color: var(--text-secondary);
            margin-bottom: 25px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        @media (max-width: 768px) {
            .order-form-container {
                margin: 20px auto;
                padding: 0 12px;
            }

            .order-form-box {
                padding: 20px 16px;
            }

            .order-form-box h1 {
                font-size: 26px;
                line-height: 1.2;
            }

            .login-footer {
                margin-top: 12px;
                padding-top: 14px;
            }

            .login-footer p {
                word-break: break-word;
            }
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
                        <option value="standard">Standard (Regular Queue)</option>
                        <option value="priority">Priority (Night Shift - Additional Fee)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sample_type">Sample Type *</label>
                    <select id="sample_type" name="sample_type" required>
                        <option value="">Select type...</option>
                        <option value="ore">Ore (30 min prep time)</option>
                        <option value="liquid">Liquid (No prep needed)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="compound_name">Compound Name *</label>
                    <input 
                        type="text" 
                        id="compound_name" 
                        name="compound_name" 
                        required 
                        placeholder="e.g., Iron Oxide, Sulfuric Acid"
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
                        >
                    </div>

                    <div class="form-group">
                        <label for="unit">Unit *</label>
                        <select id="unit" name="unit" required>
                            <option value="">Select unit...</option>
                            <option value="g">Grams (g)</option>
                            <option value="kg">Kilograms (kg)</option>
                            <option value="mL">Milliliters (mL)</option>
                            <option value="L">Liters (L)</option>
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
    <script src="js/main.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>
