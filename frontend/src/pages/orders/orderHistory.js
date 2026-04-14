import { initPageBootstrap } from '../../utils/pageBootstrap.js';
import { attachRequiredFieldValidation } from '../../utils/formValidation.js';
import { initAutoHideAlerts } from '../../utils/alerts.js';
import { bindOrderDetailsButtons } from '../../utils/orderDetailsDialog.js';

document.addEventListener('DOMContentLoaded', function () {
    initPageBootstrap();
    attachRequiredFieldValidation();
    initAutoHideAlerts();
    bindOrderDetailsButtons();

    const fromDate = document.querySelector('input[name="date_from"]');
    const toDate = document.querySelector('input[name="date_to"]');
    const quickRange = document.getElementById('quick_range');

    const today = new Date();

    // last day of current month
    const currentMonthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    // one year back from today
    const oneYearAgo = new Date(today);
    oneYearAgo.setFullYear(today.getFullYear() - 1);

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const minDate = formatDate(oneYearAgo);
    const maxDate = formatDate(currentMonthEnd);

    if (fromDate && toDate) {
        fromDate.min = minDate;
        fromDate.max = maxDate;

        toDate.min = minDate;
        toDate.max = maxDate;
    }

    if (quickRange && fromDate && toDate) {
        quickRange.addEventListener('change', function () {
            const endDate = new Date(currentMonthEnd);
            const startDate = new Date(currentMonthEnd);

            if (this.value === '7days') {
                startDate.setDate(endDate.getDate() - 6);
            } else if (this.value === '30days') {
                startDate.setDate(endDate.getDate() - 29);
            } else if (this.value === '6months') {
                startDate.setMonth(endDate.getMonth() - 6);
                startDate.setDate(startDate.getDate() + 1);
            } else if (this.value === '1year') {
                startDate.setFullYear(endDate.getFullYear() - 1);
                startDate.setDate(startDate.getDate() + 1);
            } else {
                return;
            }

            fromDate.value = formatDate(startDate);
            toDate.value = formatDate(endDate);
        });
    }

    if (toDate && fromDate) {
        toDate.addEventListener('change', function () {
            if (fromDate.value && toDate.value < fromDate.value) {
                alert('To date cannot be earlier than From date');
                toDate.value = '';
            }
        });

        fromDate.addEventListener('change', function () {
            if (toDate.value && fromDate.value > toDate.value) {
                alert('From date cannot be later than To date');
                fromDate.value = '';
            }
        });
    }
});
