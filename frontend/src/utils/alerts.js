/**
 * Auto-dismiss flash alerts. Import on pages that render `.alert` elements.
 */
export function initAutoHideAlerts(options = {}) {
    const delayMs = options.delayMs ?? 5000;
    const fadeMs = options.fadeMs ?? 500;
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = `opacity ${fadeMs / 1000}s`;
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, fadeMs);
        }, delayMs);
    });
}
