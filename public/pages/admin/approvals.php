<?php
declare(strict_types=1);

$__adminTitle = 'Pending Approvals';
require __DIR__ . '/_init.php';
include __DIR__ . '/_html_start.php';
?>
<!-- Pending Approvals – same order approval page for Admin and Technician -->
                <section class="admin-section">
                    <h1>Pending Approvals</h1>
                    <p class="section-desc">Review and approve or reject submitted orders.</p>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Company</th>
                                    <th>Submitted</th>
                                    <th>Priority</th>
                                    <th>Samples</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pendingOrders = $order->getPendingOrders();
                                if (empty($pendingOrders)):
                                ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No pending orders</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingOrders as $po): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($po['order_number']); ?></td>
                                        <td><?php echo htmlspecialchars($po['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($po['company_name'] ?? '-'); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($po['created_at'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $po['priority']; ?>">
                                                <?php echo ucfirst($po['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $po['sample_count']; ?></td>
                                        <td class="actions">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $po['id']; ?>">
                                                <button type="submit" name="approve_order" class="btn btn-small btn-success">Approve</button>
                                            </form>
                                            <!-- <form method="POST" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $po['id']; ?>">
                                                <input type="hidden" name="rejection_reason" value="Order rejected">
                                                <button type="submit" name="reject_order" class="btn btn-small btn-danger">Reject</button>
                                            </form> -->

                                            <form method="POST" class="reject-order-form" style="display:inline;">
                                                <input type="hidden" name="order_id" value="<?php echo $po['id']; ?>">
                                                <input type="hidden" name="rejection_reason" value="">
                                                <button type="button" class="btn btn-small btn-danger btn-open-reject-modal">
                                                    Reject
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <div class="modal-overlay" id="rejectOrderModal" aria-hidden="true">
                    <div class="modal" role="dialog" aria-labelledby="rejectOrderModalTitle">
                        <h2 id="rejectOrderModalTitle">Reject Order</h2>
                        <p style="margin-bottom: 12px;">Are you sure you want to reject this order?</p>
                        <div class="form-group">
                            <label for="reject_reason_text">Reason for customer *</label>
                            <!-- <textarea
                            id="reject_reason_text"
                            rows="4"
                            placeholder="Enter the reason for rejecting this order..."
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"
                            ></textarea>
                            <small id="rejectReasonError" style="color:#dc3545; display:none; margin-top:6px;">
                                Please enter a rejection reason.
                            </small> -->
                            <textarea
                            id="reject_reason_text"
                            rows="4"
                            placeholder="Enter the reason for rejecting this order..."
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"
                            ></textarea>
                            <small id="rejectReasonWordCount" style="display:block; margin-top:6px; color:#666;">
                                0 / 500 words
                            </small>
                        </div>
                        
                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" id="cancelRejectModal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmRejectModal">Confirm Reject</button>
                        </div>
                    </div>
                </div>
<?php include __DIR__ . '/_html_end.php'; ?>
    <script type="module" src="frontend/src/pages/admin/adminShared.js"></script>
</body>
</html>
