/**
 * Stripe.js Elements UI only — no payment API calls, no polling.
 */
export function mountStripeCard(publicKey, mountSelector) {
    if (typeof Stripe === 'undefined' || !publicKey) {
        return null;
    }
    const stripe = Stripe(publicKey);
    const elements = stripe.elements();
    const card = elements.create('card', { hidePostalCode: true });
    card.mount(mountSelector);
    return { stripe, card };
}
