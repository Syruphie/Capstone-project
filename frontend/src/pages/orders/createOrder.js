import { initPageBootstrap } from '../../utils/pageBootstrap.js';
import { attachRequiredFieldValidation } from '../../utils/formValidation.js';
import { initAutoHideAlerts } from '../../utils/alerts.js';
import { openConfirmDialog } from '../../utils/confirmDialog.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();
    attachRequiredFieldValidation();
    initAutoHideAlerts();

    const submitOrderForm = document.querySelector('form.login-form');
    const submitOrderButton = submitOrderForm?.querySelector('button[name="submit_order"]');

    if (submitOrderForm && submitOrderButton) {
        submitOrderForm.addEventListener('submit', async function (event) {
            if (submitOrderForm.dataset.confirmed === 'true') return;

            event.preventDefault();

            const isConfirmed = await openConfirmDialog({
                title: 'Submit Order',
                message: 'Are you sure you want to submit this order request?',
                confirmText: 'Submit Order',
                cancelText: 'Cancel'
            });

            if (!isConfirmed) return;

            submitOrderForm.dataset.confirmed = 'true';
            submitOrderForm.requestSubmit(submitOrderButton);
        });
    }
});
