import { fetchPaymentStatus } from './api/paymentApi.js';

/**
 * Polls backend payment status over HTTP. Lives in services/ because it performs network I/O.
 */
export function startPaymentStatusPoller(orderId, options = {}) {
    if (!orderId) {
        return function noop() {};
    }
    const intervalMs = options.intervalMs ?? 5000;
    const onUpdate = options.onUpdate;
    const onSucceeded = options.onSucceeded;

    const timer = setInterval(async () => {
        try {
            const data = await fetchPaymentStatus(orderId, true);
            if (!data.success || !data.found) return;
            if (onUpdate) onUpdate(data);
            if (data.payment && data.payment.status === 'succeeded' && onSucceeded) {
                clearInterval(timer);
                onSucceeded(data);
            }
        } catch (e) {
            /* intentionally silent for polling */
        }
    }, intervalMs);

    return function stop() {
        clearInterval(timer);
    };
}
