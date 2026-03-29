import { mountStripeCard } from '../../components/checkout/stripeElements.js';
import { fetchPaymentStatus, postStripePaymentIntent } from '../../services/api/paymentApi.js';
import { startPaymentStatusPoller } from '../../services/paymentStatusPoller.js';

/** Root-relative URL to orders/my-orders with query (respects subdirectory installs via __APP_BASE__). */
function myOrdersPaidUrl(orderId) {
    const b = typeof window.__APP_BASE__ === 'string' ? window.__APP_BASE__ : '';
    const q = 'orders/my-orders.php?paid=1&order_id=' + orderId;
    if (!b) return '/' + q;
    return b.replace(/\/?$/, '/') + q;
}

(function initStripeCheckout() {
    const root = document.body;
    const publicKey = root.dataset.stripePublicKey || '';
    const orderId = parseInt(root.dataset.orderId || '0', 10);
    const customerEmail = root.dataset.customerEmail || '';

    if (!publicKey || typeof Stripe === 'undefined') {
        const err = document.getElementById('error-message');
        if (err && !publicKey) err.textContent = 'Stripe is not configured.';
        return;
    }

    const mounted = mountStripeCard(publicKey, '#card-element');
    if (!mounted) return;
    const { stripe, card } = mounted;

    startPaymentStatusPoller(orderId, {
        onUpdate: function (data) {
            const msg = document.getElementById('status-message');
            if (msg) msg.textContent = 'Current payment status: ' + data.payment.status;
        },
        onSucceeded: function () {
            window.location.href = myOrdersPaidUrl(orderId);
        },
    });

    const form = document.getElementById('payment-form');
    const payButton = document.getElementById('pay-button');
    if (!form || !payButton) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        payButton.disabled = true;
        const errEl = document.getElementById('error-message');
        if (errEl) errEl.textContent = '';

        const data = await postStripePaymentIntent(orderId);
        if (!data.success) {
            if (errEl) errEl.textContent = data.error || 'Payment failed.';
            payButton.disabled = false;
            return;
        }

        const result = await stripe.confirmCardPayment(data.client_secret, {
            payment_method: {
                card: card,
                billing_details: { email: customerEmail },
            },
        });

        if (result.error) {
            if (errEl) errEl.textContent = result.error.message;
            payButton.disabled = false;
        } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
            try {
                await fetchPaymentStatus(orderId, true);
            } catch (e) {
                /* best-effort */
            }
            window.location.href = myOrdersPaidUrl(orderId);
        } else {
            if (errEl) errEl.textContent = 'Payment not completed.';
            payButton.disabled = false;
        }
    });
})();
