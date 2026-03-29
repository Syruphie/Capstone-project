import { endpointUrl } from './apiBase.js';

export async function fetchPaymentStatus(orderId, refresh = true) {
    const url = endpointUrl('payment-status', {
        order_id: orderId,
        refresh: refresh ? 1 : 0,
    });
    const res = await fetch(url);
    return res.json();
}

export async function postStripePaymentIntent(orderId) {
    const res = await fetch(endpointUrl('create-payment-intent'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId }),
    });
    return res.json();
}
