<?php
declare(strict_types=1);

$__adminTitle = 'Reports';
require __DIR__ . '/_init.php';
include __DIR__ . '/_html_start.php';
?>
                <section class="admin-section">
                    <h1>Performance Reports</h1>
                    <p class="section-desc">Generate and view reports on orders, revenue, equipment, and queue. Export as CSV or JSON.</p>

                    <div class="report-cards">
                        <div class="report-card" data-report-type="orders">
                            <h3>Orders Report</h3>
                            <p>View order statistics, processing times, and completion rates.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>

                        <div class="report-card" data-report-type="revenue">
                            <h3>Revenue Report</h3>
                            <p>View payment statistics, revenue trends, and financial summaries.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>

                        <div class="report-card" data-report-type="equipment">
                            <h3>Equipment Report</h3>
                            <p>View equipment utilization, delays, and maintenance history.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>

                        <div class="report-card" data-report-type="queue">
                            <h3>Queue Analytics</h3>
                            <p>View queue statistics, wait times, and processing efficiency.</p>
                            <div class="report-options">
                                <select class="form-control report-range">
                                    <option value="day">One Day</option>
                                    <option value="week">One Week</option>
                                    <option value="month">One Month</option>
                                    <option value="year">One Year</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-report-generate">Generate</button>
                            </div>
                            <div class="report-card-custom-dates" style="display:none;">
                                <input type="date" class="form-control report-from" style="margin-top:8px;">
                                <input type="date" class="form-control report-to" style="margin-top:4px;">
                            </div>
                        </div>
                    </div>

                    <div class="report-export-actions" id="reportExportActions" style="display:none;">
                        <button type="button" class="btn btn-small btn-primary" id="reportDownloadCsv">Download as CSV</button>
                        <button type="button" class="btn btn-small btn-primary" id="reportDownloadJson">Download as JSON</button>
                    </div>

                    <div class="report-output" id="reportOutput">
                        <p class="empty-state">Select a report type and time range, then click Generate to view results.</p>
                    </div>
                </section>
<?php include __DIR__ . '/_html_end.php'; ?>
    <script type="module" src="frontend/src/pages/admin/adminShared.js"></script>
    <script type="module" src="frontend/src/pages/admin/reports.js"></script>
</body>
</html>
