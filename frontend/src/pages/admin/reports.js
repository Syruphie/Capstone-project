import { fetchReport } from '../../services/api/reportsApi.js';
import { exportReportToCsv, exportReportToJson } from '../../utils/reportExport.js';
import { renderOrdersReport } from '../../components/admin/reports/renderOrdersReport.js';
import { renderRevenueReport } from '../../components/admin/reports/renderRevenueReport.js';
import { renderEquipmentReport } from '../../components/admin/reports/renderEquipmentReport.js';
import { renderQueueReport } from '../../components/admin/reports/renderQueueReport.js';

document.addEventListener('DOMContentLoaded', function () {
    var reportOutput = document.getElementById('reportOutput');
    var reportExportActions = document.getElementById('reportExportActions');
    var reportDownloadCsv = document.getElementById('reportDownloadCsv');
    var reportDownloadJson = document.getElementById('reportDownloadJson');
    var currentReportData = null;

    if (!reportOutput || !reportExportActions) return;

    document.querySelectorAll('.report-card').forEach(function (card) {
        var rangeSelect = card.querySelector('.report-range');
        var customDates = card.querySelector('.report-card-custom-dates');
        var fromInput = card.querySelector('.report-from');
        var toInput = card.querySelector('.report-to');
        rangeSelect.addEventListener('change', function () {
            customDates.style.display = this.value === 'custom' ? 'block' : 'none';
        });
        card.querySelector('.btn-report-generate').addEventListener('click', function () {
            var range = rangeSelect.value;
            if (range === 'custom' && (!fromInput.value || !toInput.value)) {
                alert('Please select custom date range.');
                return;
            }
            runReport(card.dataset.reportType, range, fromInput.value, toInput.value);
        });
    });

    function runReport(type, range, fromVal, toVal) {
        reportOutput.innerHTML = '<p class="empty-state">Loading…</p>';
        reportExportActions.style.display = 'none';
        fetchReport(type, range, fromVal, toVal)
            .then(function (res) {
                if (!res.success) {
                    reportOutput.innerHTML = '<p class="empty-state">' + (res.error || 'Error') + '</p>';
                    return;
                }
                currentReportData = res;
                var html = '';
                switch (res.report) {
                    case 'orders':
                        html = renderOrdersReport(res);
                        break;
                    case 'revenue':
                        html = renderRevenueReport(res);
                        break;
                    case 'equipment':
                        html = renderEquipmentReport(res);
                        break;
                    case 'queue':
                        html = renderQueueReport(res);
                        break;
                    default:
                        html = '<p>Unknown report</p>';
                }
                reportOutput.innerHTML = html;
                reportExportActions.style.display = 'block';
            })
            .catch(function () {
                reportOutput.innerHTML = '<p class="empty-state">Request failed.</p>';
            });
    }

    if (reportDownloadCsv) reportDownloadCsv.addEventListener('click', () => exportReportToCsv(currentReportData));
    if (reportDownloadJson) reportDownloadJson.addEventListener('click', () => exportReportToJson(currentReportData));
});
