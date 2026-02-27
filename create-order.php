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
    <link rel="stylesheet" href="css/style.css">
    <style>
        <style> :root {
            --text: #0f172a;
            --muted: #64748b;
            --border: rgba(15, 23, 42, .10);
        }

        /* SAME WIDTH + SPACING AS OTHER PAGES */
        .order-form-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        /* SHARP OUTER CARD */
        .order-form-box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            /* sharp outside */
            padding: 32px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
        }

        .order-form-box h1 {
            margin: 0 0 8px;
            font-size: 46px;
            font-weight: 700;
            letter-spacing: -1px;
            color: var(--text);
        }

        .order-form-box .subtitle {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.6;
        }

        /* FORM SPACING */
        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 800;
            font-size: 14px;
            margin-bottom: 6px;
            color: var(--text);
        }

        /* INPUTS (ROUNDED INSIDE) */
        .login-form input,
        .login-form select {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, .14);
            font-size: 14px;
            outline: none;
            transition: all .2s ease;
            background: #fff;
        }

        .login-form input:focus,
        .login-form select:focus {
            border-color: #5b4ae6;
            box-shadow: 0 0 0 4px rgba(91, 74, 230, .14);
        }

        /* GRID ROW */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 6px;
        }

        /* BUTTON (ROUNDED) */
        .btn {
            border-radius: 16px;
            font-weight: 900;
            padding: 12px 16px;
        }

        .btn.btn-primary {
            padding: 14px 18px;
        }

        /* ALERTS */
        .alert {
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 18px;
            border: 1px solid var(--border);
        }

        /* FOOTER LINK */
        .login-footer {
            margin-top: 18px;
            color: var(--muted);
        }

        .login-footer a {
            text-decoration: none;
            font-weight: 900;
        }

        @media (max-width:900px) {
            .order-form-container {
                padding: 30px 16px;
            }

            .order-form-box h1 {
                font-size: 34px;
            }
        }

        @media (max-width:500px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                    <input type="text" id="compound_name" name="compound_name" required
                        placeholder="e.g., Iron Oxide, Sulfuric Acid">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" required min="0.01" step="0.01"
                            placeholder="Amount">
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
    <script src="js/main.js"></script>
</body>

</html>