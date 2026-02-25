<?php
// Stripe Elements Checkout Page
require_once 'stripe_env_loader.php';
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
    <form id="payment-form">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Card Details:</label>
            <div id="card-element"></div>
        </div>
        <button type="submit">Pay $20.00</button>
        <div id="error-message" class="error"></div>
    </form>
    <script>
        const stripe = Stripe('<?php echo $publicKey; ?>');
        const elements = stripe.elements();
        const card = elements.create('card');
        card.mount('#card-element');

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            // Step 1: Create PaymentIntent on backend
            const response = await fetch('process-stripe-payment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ email })
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
                window.location.href = 'success.php';
            } else {
                document.getElementById('error-message').textContent = 'Payment not completed.';
            }
        });
    </script>
</body>
</html>
