<?php
declare(strict_types=1);

$__adminTitle = 'Equipment';
require __DIR__ . '/_init.php';
include __DIR__ . '/_html_start.php';
?>
<section class="admin-section">

    <div class="equipment-header">
        <div>
            <h1>Manage Equipment</h1>
            <p class="section-desc">Configure equipment settings, processing times, and schedules.</p>
        </div>
        <button class="btn btn-primary btn-small add-equipment-btn">Add Equipment</button>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Processing</th>
                    <th>Warmup</th>
                    <th>Break</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $equipmentList = $equipment->getAllEquipment();
                if (empty($equipmentList)):
                ?>
                <tr>
                    <td colspan="8" class="empty-state">No equipment configured</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($equipmentList as $eq): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($eq['name']); ?></td>
                        <td><?php echo htmlspecialchars($eq['equipment_type']); ?></td>
                        <td><?php echo $eq['processing_time_per_sample']; ?> min</td>
                        <td><?php echo $eq['warmup_time']; ?> min</td>
                        <td><?php echo $eq['break_interval']; ?></td>
                        <td><?php echo $eq['daily_capacity']; ?></td>
                        <td>
                            <span class="status-pill <?php echo $eq['is_available'] ? 'available' : 'unavailable'; ?>">
                                <?php echo $eq['is_available'] ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button
                            class="btn btn-xs btn-secondary btn-edit-equipment"
                            data-name="<?php echo htmlspecialchars($eq['name']); ?>"
                            data-type="<?php echo htmlspecialchars($eq['equipment_type']); ?>"
                            data-processing="<?php echo (int)$eq['processing_time_per_sample']; ?>"
                            data-warmup="<?php echo (int)$eq['warmup_time']; ?>"
                            data-break-interval="<?php echo (int)$eq['break_interval']; ?>"
                            data-break-duration="<?php echo (int)$eq['break_duration']; ?>"
                            data-capacity="<?php echo (int)$eq['daily_capacity']; ?>"
                            data-available="<?php echo $eq['is_available'] ? '1' : '0'; ?>">
                            Edit
                            </button>
                             
                            <button class="btn btn-xs btn-warning btn-delay-equipment"> Delay </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

    <div class="modal-overlay" id="addEquipmentModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="addEquipmentModalTitle">
        <h2 id="addEquipmentModalTitle">Add Equipment</h2>
        <form id="addEquipmentForm">
            <div class="form-group">
                <label for="eq_name">Name *</label>
                <input type="text" id="eq_name" name="name" required maxlength="255" placeholder="e.g. ICP Spectrometer">
            </div>
            <div class="form-group">
                <label for="eq_type">Equipment Type *</label>
                <input type="text" id="eq_type" name="equipment_type" required maxlength="100" placeholder="e.g. ICP, XRF">
            </div>
            <div class="form-group">
                <label for="eq_processing">Processing Time per Sample (min) *</label>
                <input type="number" id="eq_processing" name="processing_time_per_sample" required min="0" value="2">
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="eq_warmup">Warmup Time (min)</label>
                    <input type="number" id="eq_warmup" name="warmup_time" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="eq_capacity">Daily Capacity</label>
                    <input type="number" id="eq_capacity" name="daily_capacity" min="0" value="0">
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="eq_break_interval">Break Interval (samples)</label>
                    <input type="number" id="eq_break_interval" name="break_interval" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="eq_break_duration">Break Duration (min)</label>
                    <input type="number" id="eq_break_duration" name="break_duration" min="0" value="0">
                </div>
            </div>
            <div class="form-group">
                <label for="eq_last_maintenance">Last Maintenance (optional)</label>
                <input type="date" id="eq_last_maintenance" name="last_maintenance">
            </div>
            <div class="form-group form-group-checkbox">
                <label><input type="checkbox" name="is_available" id="eq_available" checked> Available</label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="addEquipmentCancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Equipment</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editEquipmentModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="editEquipmentModalTitle">
        <h2 id="editEquipmentModalTitle">Edit Equipment</h2>
        <form id="editEquipmentForm">
            <div class="form-group">
                <label for="edit_eq_name">Name *</label>
                <input type="text" id="edit_eq_name" name="name" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="edit_eq_type">Equipment Type *</label>
                <input type="text" id="edit_eq_type" name="equipment_type" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="edit_eq_processing">Processing Time per Sample (min) *</label>
                <input type="number" id="edit_eq_processing" name="processing_time_per_sample" required min="0">
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="edit_eq_warmup">Warmup Time (min)</label>
                    <input type="number" id="edit_eq_warmup" name="warmup_time" min="0">
                </div>
                <div class="form-group">
                    <label for="edit_eq_capacity">Daily Capacity</label>
                    <input type="number" id="edit_eq_capacity" name="daily_capacity" min="0">
                </div>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="edit_eq_break_interval">Break Interval (samples)</label>
                    <input type="number" id="edit_eq_break_interval" name="break_interval" min="0">
                </div>
                <div class="form-group">
                    <label for="edit_eq_break_duration">Break Duration (min)</label>
                    <input type="number" id="edit_eq_break_duration" name="break_duration" min="0">
                </div>
            </div>
            <div class="form-group form-group-checkbox">
                <label><input type="checkbox" name="is_available" id="edit_eq_available"> Available</label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="editEquipmentCancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/_html_end.php'; ?>
    <script type="module" src="frontend/src/pages/admin/adminShared.js"></script>
    <script type="module" src="frontend/src/pages/admin/equipment.js"></script>
</body>
</html>
