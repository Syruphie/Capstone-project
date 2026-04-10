import { initPageBootstrap } from '../../utils/pageBootstrap.js';
import { attachRequiredFieldValidation } from '../../utils/formValidation.js';
import { initAutoHideAlerts } from '../../utils/alerts.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();
    attachRequiredFieldValidation();
    initAutoHideAlerts();

    const settingsPage = document.querySelector('.settings-page');
    if (!settingsPage) return;

    const dangerForms = settingsPage.querySelectorAll('form[onsubmit*="deactivate"]');
    dangerForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const ok = window.confirm('Are you sure you want to deactivate your account?');
            if (!ok) {
                e.preventDefault();
            }
        });
    });
});