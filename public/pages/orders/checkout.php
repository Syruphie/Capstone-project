<?php
require_once __DIR__ . '/../bootstrap_paths.php';
require_once CAPSTONE_PROJECT_ROOT . '/src/config/stripe_env_loader.php';

$user = new FrontendUser();
if (!$user->isLoggedIn()) {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$orderAmount = 20.00;
$order = null;
$customerId = (int) ($_SESSION['user_id'] ?? 0);
$customerEmail = '';

$customer = $user->getUserById($customerId);
if (!$customer || empty($customer['email'])) {
    http_response_code(400);
    echo 'Unable to resolve customer email for payment.';
    exit;
}
$customerEmail = (string) $customer['email'];

if ($orderId > 0) {
    $orderModel = new FrontendOrder();
    $order = $orderModel->getOrderById($orderId);
    if (!$order || (int) $order['customer_id'] !== (int) $_SESSION['user_id']) {
        http_response_code(403);
        echo 'Invalid order selected for payment.';
        exit;
    }
    $orderAmount = (float) ($order['total_cost'] ?? 20.00);
    if ($orderAmount <= 0) {
        $orderAmount = 20.00;
    }
}

$publicKey = $_ENV['STRIPE_PUBLIC_KEY'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Stripe Payment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --border: #e5e7eb;
            --ok: #0f766e;
            --error: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: linear-gradient(180deg, #eef4ff 0%, var(--bg) 60%);
            color: var(--text);
        }

        .checkout-wrap {
            max-width: 920px;
            margin: 36px auto;
            padding: 0 16px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 20px;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
            padding: 22px;
        }

        .panel h2, .panel h3 {
            margin: 0 0 10px 0;
        }

        .muted {
            color: var(--muted);
            margin: 0;
        }

        .summary-list {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed var(--border);
            padding-bottom: 8px;
            font-size: 14px;
        }

        .summary-total {
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 700;
        }

        .chip {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 10px;
            background: #e0ebff;
            color: #1e40af;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .field {
            margin-top: 14px;
        }

        .field label {
            display: block;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .field-value {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            background: #f9fafb;
            font-size: 14px;
        }

        #card-element {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            background: #fff;
        }

        .btn-pay {
            width: 100%;
            margin-top: 16px;
            border: 0;
            border-radius: 10px;
            padding: 12px 14px;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-pay:hover { background: var(--primary-hover); }
        .btn-pay:disabled { opacity: 0.7; cursor: not-allowed; }

        .error {
            color: var(--error);
            margin-top: 10px;
            min-height: 20px;
            font-size: 14px;
        }

        .status {
            color: var(--ok);
            margin-top: 8px;
            min-height: 20px;
            font-size: 14px;
        }

        .back-link {
            display: inline-block;
            margin-top: 14px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }

        @media (max-width: 800px) {
            .checkout-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body
    data-stripe-public-key="<?php echo htmlspecialchars($publicKey, ENT_QUOTES, 'UTF-8'); ?>"
    data-order-id="<?php echo (int) $orderId; ?>"
    data-customer-email="<?php echo htmlspecialchars($customerEmail, ENT_QUOTES, 'UTF-8'); ?>"
>
    <div class="checkout-wrap">
        <div class="checkout-grid">
            <section class="panel">
                <h2>Secure Checkout</h2>
                <p class="muted">Card payments are processed securely using Stripe.</p>
                <span class="chip">Currency: CAD</span>

                <div class="field">
                    <label>Customer Email</label>
                    <div class="field-value"><?php echo htmlspecialchars($customerEmail); ?></div>
                </div>

                <div class="field">
                    <label>Card Details</label>
                    <div id="card-element"></div>
                </div>

                <form id="payment-form">
                    <button id="pay-button" class="btn-pay" type="submit">Pay $<?php echo number_format($orderAmount, 2); ?></button>
                    <div id="error-message" class="error"></div>
                    <div id="status-message" class="status"></div>
                </form>

                <a class="back-link" href="<?php echo htmlspecialchars(app_path('orders/my-orders.php'), ENT_QUOTES, 'UTF-8'); ?>">&larr; Back to My Orders</a>
            </section>

            <aside class="panel">
                <h3>Order Summary</h3>
                <div class="summary-list">
                    <div class="summary-item">
                        <span>Order Number</span>
                        <strong><?php echo htmlspecialchars($order['order_number'] ?? 'N/A'); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Order ID</span>
                        <strong><?php echo $orderId > 0 ? (int) $orderId : 'N/A'; ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Payment Method</span>
                        <strong>Credit / Debit Card</strong>
                    </div>
                </div>

                <div class="summary-total">
                    <span>Total</span>
                    <span>$<?php echo number_format($orderAmount, 2); ?> CAD</span>
                </div>
                <p class="muted" style="margin-top: 10px; font-size: 13px;">Postal/ZIP entry is disabled for this flow.</p>
            </aside>
        </div>
    </div>
    <script type="module" src="frontend/src/pages/checkout/stripeCheckout.js"></script>
</body>
</html>
