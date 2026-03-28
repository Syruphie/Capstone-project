<?php
// Stripe Elements Checkout Page
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/classes/Frontend/bootstrap.php';
require_once __DIR__ . '/src/config/stripe_env_loader.php';

$user = new FrontendUser();
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$orderAmount = 20.00;
$order = null;

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
    <title>Checkout - Stripe Payment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-group { margin-bottom: 15px; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Lab Analysis Payment</h2>
    <?php if ($orderId > 0 && $order): ?>
        <p>Order: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong></p>
    <?php endif; ?>
    <form id="payment-form">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Card Details:</label>
            <div id="card-element"></div>
        </div>
        <button type="submit">Pay $<?php echo number_format($orderAmount, 2); ?></button>
        <div id="error-message" class="error"></div>
        <div id="status-message"></div>
    </form>
    <script>
        const stripe = Stripe('<?php echo $publicKey; ?>');
        const orderId = <?php echo (int) $orderId; ?>;
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        async function pollPaymentStatus() {
            if (!orderId) return;

            try {
                const res = await fetch(`api.php?endpoint=payment-status&order_id=${orderId}&refresh=1`);
                const data = await res.json();
                if (!data.success || !data.found) return;

                const msg = document.getElementById('status-message');
                msg.textContent = `Current payment status: ${data.payment.status}`;

                if (data.payment.status === 'succeeded') {
                    window.location.href = `my-orders.php?paid=1&order_id=${orderId}`;
                }
            } catch (e) {
                // Intentionally silent for polling
            }
        }

        setInterval(pollPaymentStatus, 5000);

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            // Step 1: Create PaymentIntent on backend
            const response = await fetch('api.php?endpoint=create-payment-intent', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ email, order_id: orderId })
            });
            const data = await response.json();
            if (!data.success) {
                document.getElementById('error-message').textContent = data.error || 'Payment failed.';
                return;
            }
            // Step 2: Confirm payment on frontend
            const result = await stripe.confirmCardPayment(data.client_secret, {
                payment_method: {
                    card: card,
                    billing_details: { email }
                }
            });
            if (result.error) {
                document.getElementById('error-message').textContent = result.error.message;
            } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                try {
                    await fetch(`api.php?endpoint=payment-status&order_id=${orderId}&refresh=1`);
                } catch (e) {
                    // Best-effort sync; redirect anyway to preserve UX.
                }
                window.location.href = `my-orders.php?paid=1&order_id=${orderId}`;
            } else {
                document.getElementById('error-message').textContent = 'Payment not completed.';
            }
        });
    </script>
</body>
</html>


