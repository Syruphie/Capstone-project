<?php
require_once __DIR__ . '/../bootstrap_paths.php';
require_once PAGE_HANDLERS . '/create-order-process-post.php';

$user = new FrontendUser();

if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}

$userId = (int) $_SESSION['user_id'];
$orderTypeModel = new FrontendOrderType();
$orderTypes = $orderTypeModel->getAll(true);

$result = create_order_process_post($userId, $orderTypes);
$error = $result['error'];
$success = $result['success'];
$priority = $result['priority'];
$unit = $result['unit'];
$orderNote = $result['orderNote'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
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
    <?php include PAGE_PARTIALS . '/header.php'; ?>

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
                    <br><a href="<?php echo htmlspecialchars(app_path('dashboard/index.php'), ENT_QUOTES, 'UTF-8'); ?>">Return to Dashboard</a>
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
                        <option value="<?php echo (int) $ot['id']; ?>" data-cost="<?php echo number_format((float) $ot['cost'], 2); ?>" <?php echo (isset($_POST['order_type_id']) && (int) $_POST['order_type_id'] === (int) $ot['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ot['name']); ?> — $<?php echo number_format((float) $ot['cost'], 2); ?>
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

                <div class="form-group">
                    <label for="order_note">Description / Note (optional)</label>
                    <textarea
                        id="order_note"
                        name="order_note"
                        rows="4"
                        maxlength="1000"
                        placeholder="Add sample specifications, handling instructions, or extra details."
                    ><?php echo htmlspecialchars($orderNote ?? ''); ?></textarea>
                </div>

                <button type="submit" name="submit_order" class="btn btn-primary">Submit Order</button>
            </form>

            <div class="login-footer">
                <p><a href="<?php echo htmlspecialchars(app_path('dashboard/index.php'), ENT_QUOTES, 'UTF-8'); ?>">← Back to Dashboard</a></p>
            </div>
        </div>
    </div>

    <?php include PAGE_PARTIALS . '/footer.php'; ?>
    <script type="module" src="frontend/src/pages/orders/createOrder.js"></script>
</body>
</html>
