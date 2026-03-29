<?php
declare(strict_types=1);

$__adminTitle = 'Order Catalogue';
require __DIR__ . '/_init.php';
$orderType = new FrontendOrderType();
include __DIR__ . '/_html_start.php';
?>
                <section class="admin-section">
                    <div class="equipment-header">
                        <div>
                            <h1>Order Catalogue</h1>
                            <p class="section-desc">Create and manage order types that customers can select when placing orders. Each type has a configurable cost.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-small" id="addOrderTypeBtn">Add Order Type</button>
                    </div>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Cost</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="orderTypesTableBody">
                                <?php
                                $typesList = $orderType->getAll(false);
                                if (empty($typesList)):
                                ?>
                                <tr>
                                    <td colspan="6" class="empty-state">No order types. Add one to get started.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($typesList as $ot): ?>
                                    <tr data-id="<?php echo (int) $ot['id']; ?>">
                                        <td><?php echo htmlspecialchars($ot['name']); ?></td>
                                        <td><?php echo isset($ot['sample_type']) ? ucfirst($ot['sample_type']) : 'Ore'; ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($ot['description'] ?? '', 0, 60)); ?><?php echo mb_strlen($ot['description'] ?? '') > 60 ? '…' : ''; ?></td>
                                        <td><?php echo number_format((float) $ot['cost'], 2); ?></td>
                                        <td>
                                            <span class="status-pill <?php echo $ot['is_active'] ? 'available' : 'unavailable'; ?>">
                                                <?php echo $ot['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button type="button" class="btn btn-xs btn-secondary btn-edit-type" data-id="<?php echo (int) $ot['id']; ?>">Edit</button>
                                            <button type="button" class="btn btn-xs btn-danger btn-delete-type" data-id="<?php echo (int) $ot['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <div class="modal-overlay" id="orderTypeModal" aria-hidden="true">
                    <div class="modal" role="dialog">
                        <h2 id="orderTypeModalTitle">Add Order Type</h2>
                        <form id="orderTypeForm">
                            <input type="hidden" id="ot_id" name="id" value="">
                            <div class="form-group">
                                <label for="ot_name">Name *</label>
                                <input type="text" id="ot_name" name="name" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="ot_description">Description</label>
                                <textarea id="ot_description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="ot_sample_type">Sample Type *</label>
                                <select id="ot_sample_type" name="sample_type" required>
                                    <option value="ore">Ore (30 min prep)</option>
                                    <option value="liquid">Liquid (no prep)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ot_cost">Cost (<?php echo htmlspecialchars('$'); ?>) *</label>
                                <input type="number" id="ot_cost" name="cost" required min="0" step="0.01" value="0">
                            </div>
                            <div class="form-group form-group-checkbox" id="ot_activeWrap">
                                <label><input type="checkbox" name="is_active" id="ot_active" checked> Active</label>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn btn-secondary" id="orderTypeModalCancel">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
<?php include __DIR__ . '/_html_end.php'; ?>
    <script type="module" src="frontend/src/pages/admin/adminShared.js"></script>
    <script type="module" src="frontend/src/pages/admin/catalogue.js"></script>
</body>
</html>
