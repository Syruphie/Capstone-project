<?php
declare(strict_types=1);

$__adminTitle = 'Samples';
require __DIR__ . '/_init.php';
include __DIR__ . '/_html_start.php';
?>
                <section class="admin-section">
                    <h1>Manage Samples</h1>
                    <p class="section-desc">View and manage sample processing status.</p>

                    <div class="filter-bar">
                        <select class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready">Ready</option>
                            <option value="testing">Testing</option>
                            <option value="completed">Completed</option>
                        </select>
                        <select class="form-control">
                            <option value="">All Types</option>
                            <option value="ore">Ore</option>
                            <option value="liquid">Liquid</option>
                        </select>
                        <button class="btn btn-secondary">Filter</button>
                    </div>

                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Sample ID</th>
                                    <th>Order #</th>
                                    <th>Type</th>
                                    <th>Compound</th>
                                    <th>Quantity</th>
                                    <th>Prep Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="empty-state">No samples found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
<?php include __DIR__ . '/_html_end.php'; ?>
    <script type="module" src="frontend/src/pages/admin/adminShared.js"></script>
</body>
</html>
