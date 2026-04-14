let detailsModal = null;

function ensureDetailsModal() {
    if (detailsModal) return detailsModal;

    const overlay = document.createElement('div');
    overlay.setAttribute('aria-hidden', 'true');
    Object.assign(overlay.style, {
        position: 'fixed',
        inset: '0',
        background: 'rgba(0, 0, 0, 0.45)',
        display: 'none',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: '2100',
        padding: '20px'
    });

    const dialog = document.createElement('div');
    dialog.setAttribute('role', 'dialog');
    dialog.setAttribute('aria-modal', 'true');
    Object.assign(dialog.style, {
        width: '100%',
        maxWidth: '620px',
        background: '#fff',
        borderRadius: '10px',
        padding: '20px',
        boxShadow: '0 12px 30px rgba(0, 0, 0, 0.2)',
        maxHeight: '90vh',
        overflowY: 'auto'
    });

    const title = document.createElement('h2');
    title.textContent = 'Order Details';
    title.style.margin = '0 0 16px 0';

    const body = document.createElement('div');

    const actions = document.createElement('div');
    Object.assign(actions.style, { display: 'flex', justifyContent: 'flex-end', marginTop: '16px' });

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn btn-secondary';
    closeButton.textContent = 'Close';

    actions.appendChild(closeButton);
    dialog.append(title, body, actions);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    detailsModal = { overlay, body, closeButton };
    return detailsModal;
}

function line(label, value) {
    const safeValue = value === null || value === undefined || value === '' ? '—' : String(value);
    return '<p style="margin:0 0 8px 0;"><strong>' + label + ':</strong> ' + safeValue + '</p>';
}

function toDateText(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleString();
}

export function openOrderDetailsDialog(order) {
    const modal = ensureDetailsModal();

    const sampleSummary = order.sampleSummary
        || (Array.isArray(order.sampleTypes) && order.sampleTypes.length ? order.sampleTypes.join(', ') : null)
        || order.sampleCount;

    modal.body.innerHTML = [
        line('Order #', order.orderNumber),
        line('Status', order.status),
        line('Priority', order.priority),
        line('Customer', order.customerName),
        line('Company', order.companyName),
        line('Sample Summary', sampleSummary),
        line('Submitted', toDateText(order.createdAt)),
        line('Estimated Completion', toDateText(order.estimatedCompletion)),
        line('Approved At', toDateText(order.approvedAt)),
        line('Completed At', toDateText(order.completedAt)),
        line('Rejection Reason', order.rejectionReason),
        line('Description / Note', order.orderNote)
    ].join('');

    modal.overlay.style.display = 'flex';
    modal.overlay.setAttribute('aria-hidden', 'false');

    return new Promise((resolve) => {
        const cleanup = () => {
            modal.overlay.style.display = 'none';
            modal.overlay.setAttribute('aria-hidden', 'true');
            modal.overlay.removeEventListener('click', onOverlayClick);
            modal.closeButton.removeEventListener('click', onClose);
            document.removeEventListener('keydown', onEscape);
        };

        const onClose = () => {
            cleanup();
            resolve();
        };

        const onOverlayClick = (event) => {
            if (event.target === modal.overlay) onClose();
        };

        const onEscape = (event) => {
            if (event.key === 'Escape') onClose();
        };

        modal.overlay.addEventListener('click', onOverlayClick);
        modal.closeButton.addEventListener('click', onClose);
        document.addEventListener('keydown', onEscape);
    });
}

export function bindOrderDetailsButtons(selector = '.btn-view-order-details') {
    document.querySelectorAll(selector).forEach((button) => {
        button.addEventListener('click', () => {
            const raw = button.getAttribute('data-order-details');
            if (!raw) return;
            try {
                const data = JSON.parse(raw);
                openOrderDetailsDialog(data);
            } catch (_error) {
                // Do nothing if malformed payload is provided.
            }
        });
    });
}
