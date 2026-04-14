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

    if (confirmSubmitOrderModal && createOrderForm) {
        confirmSubmitOrderModal.addEventListener('click', function () {
            isSubmittingConfirmed = true;
            closeSubmitModal();
            createOrderForm.requestSubmit();
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
