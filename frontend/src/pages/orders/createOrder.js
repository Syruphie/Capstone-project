import { initPageBootstrap } from '../../utils/pageBootstrap.js';
import { attachRequiredFieldValidation } from '../../utils/formValidation.js';
import { initAutoHideAlerts } from '../../utils/alerts.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();
    attachRequiredFieldValidation();
    initAutoHideAlerts();

    const createOrderForm = document.getElementById('createOrderForm');
    const submitOrderModal = document.getElementById('submitOrderModal');
    const cancelSubmitOrderModal = document.getElementById('cancelSubmitOrderModal');
    const confirmSubmitOrderModal = document.getElementById('confirmSubmitOrderModal');
    let isSubmittingConfirmed = false;
    let isFinalSubmitInProgress = false;

    /**
     * Must pass the real submit button so the POST includes submit_order (PHP checks isset($_POST['submit_order'])).
     * requestSubmit() with no argument can omit the submitter in some browsers.
     */
    function submitFormCompat(form, submitter) {
        if (!form) return;
        if (typeof form.requestSubmit === 'function') {
            try {
                if (submitter) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            } catch (_e) {
                ensureSubmitOrderFlag(form);
                form.submit();
            }
            return;
        }
        ensureSubmitOrderFlag(form);
        form.submit();
    }

    function ensureSubmitOrderFlag(form) {
        if (form.querySelector('input[type="hidden"][name="submit_order"]')) return;
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'submit_order';
        hidden.value = '1';
        form.appendChild(hidden);
    }

    function openSubmitModal() {
        if (submitOrderModal) {
            submitOrderModal.setAttribute('aria-hidden', 'false');
        }
    }

    function closeSubmitModal() {
        if (submitOrderModal) {
            submitOrderModal.setAttribute('aria-hidden', 'true');
        }
    }

    if (createOrderForm && submitOrderModal) {
        createOrderForm.addEventListener('submit', function (event) {
            if (isSubmittingConfirmed) {
                return;
            }

            event.preventDefault();
            openSubmitModal();
        });
    }

    if (cancelSubmitOrderModal) {
        cancelSubmitOrderModal.addEventListener('click', function () {
            closeSubmitModal();
        });
    }

    const submitOrderButton = createOrderForm
        ? createOrderForm.querySelector('button[name="submit_order"]')
        : null;

    if (confirmSubmitOrderModal && createOrderForm) {
        confirmSubmitOrderModal.addEventListener('click', function () {
            if (isFinalSubmitInProgress) {
                return;
            }
            isFinalSubmitInProgress = true;
            confirmSubmitOrderModal.disabled = true;
            isSubmittingConfirmed = true;
            closeSubmitModal();
            try {
                submitFormCompat(createOrderForm, submitOrderButton);
            } catch (_e) {
                isSubmittingConfirmed = false;
                isFinalSubmitInProgress = false;
                confirmSubmitOrderModal.disabled = false;
            }
        });
    }

    if (submitOrderModal) {
        submitOrderModal.addEventListener('click', function (event) {
            if (event.target === submitOrderModal) {
                closeSubmitModal();
            }
        });
    }
});
