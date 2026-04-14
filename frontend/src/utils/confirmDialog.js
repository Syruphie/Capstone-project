let modalElements = null;

function ensureModal() {
    if (modalElements) return modalElements;

    const overlay = document.createElement('div');
    overlay.setAttribute('aria-hidden', 'true');
    Object.assign(overlay.style, {
        position: 'fixed',
        inset: '0',
        background: 'rgba(0, 0, 0, 0.45)',
        display: 'none',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: '2000',
        padding: '20px'
    });

    const dialog = document.createElement('div');
    dialog.setAttribute('role', 'dialog');
    dialog.setAttribute('aria-modal', 'true');
    Object.assign(dialog.style, {
        width: '100%',
        maxWidth: '460px',
        background: '#fff',
        borderRadius: '10px',
        padding: '20px',
        boxShadow: '0 12px 30px rgba(0, 0, 0, 0.2)'
    });

    const title = document.createElement('h2');
    Object.assign(title.style, { margin: '0 0 10px 0', fontSize: '20px' });

    const message = document.createElement('p');
    Object.assign(message.style, { margin: '0 0 16px 0', color: '#555' });

    const actions = document.createElement('div');
    Object.assign(actions.style, { display: 'flex', justifyContent: 'flex-end', gap: '10px' });

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.className = 'btn btn-secondary';
    cancelButton.textContent = 'Cancel';

    const confirmButton = document.createElement('button');
    confirmButton.type = 'button';
    confirmButton.className = 'btn btn-primary';
    confirmButton.textContent = 'Confirm';

    actions.append(cancelButton, confirmButton);
    dialog.append(title, message, actions);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    modalElements = { overlay, title, message, cancelButton, confirmButton };
    return modalElements;
}

export function openConfirmDialog({
    title = 'Please Confirm',
    message = 'Are you sure you want to continue?',
    confirmText = 'Confirm',
    cancelText = 'Cancel'
} = {}) {
    const modal = ensureModal();
    modal.title.textContent = title;
    modal.message.textContent = message;
    modal.confirmButton.textContent = confirmText;
    modal.cancelButton.textContent = cancelText;
    modal.overlay.style.display = 'flex';
    modal.overlay.setAttribute('aria-hidden', 'false');

    return new Promise((resolve) => {
        const cleanup = () => {
            modal.overlay.style.display = 'none';
            modal.overlay.setAttribute('aria-hidden', 'true');
            modal.overlay.removeEventListener('click', onOverlayClick);
            modal.cancelButton.removeEventListener('click', onCancel);
            modal.confirmButton.removeEventListener('click', onConfirm);
            document.removeEventListener('keydown', onEscape);
        };

        const onCancel = () => {
            cleanup();
            resolve(false);
        };

        const onConfirm = () => {
            cleanup();
            resolve(true);
        };

        const onOverlayClick = (event) => {
            if (event.target === modal.overlay) onCancel();
        };

        const onEscape = (event) => {
            if (event.key === 'Escape') onCancel();
        };

        modal.overlay.addEventListener('click', onOverlayClick);
        modal.cancelButton.addEventListener('click', onCancel);
        modal.confirmButton.addEventListener('click', onConfirm);
        document.addEventListener('keydown', onEscape);
    });
}
