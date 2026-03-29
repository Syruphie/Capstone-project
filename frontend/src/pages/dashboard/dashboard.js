import { initPageBootstrap, initDashboardCardAnimations } from '../../utils/pageBootstrap.js';
import { attachRequiredFieldValidation } from '../../utils/formValidation.js';
import { initAutoHideAlerts } from '../../utils/alerts.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();
    initDashboardCardAnimations();
    attachRequiredFieldValidation();
    initAutoHideAlerts();
});
