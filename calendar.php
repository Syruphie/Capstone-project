<?php
require_once 'config/database.php';
require_once 'classes/User.php';

$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$role = $user->getRole();
if (!in_array($role, ['administrator', 'technician'], true)) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar & Queue - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="css/calendar.css?v=<?php echo ASSET_VERSION; ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="calendar-container">
        <main class="calendar-content">
            <section class="calendar-header">
                <h1>Calendar & Queue</h1>
                <p class="section-desc">Scheduled orders, equipment utilization, and queue management.</p>
                <div class="calendar-toolbar">
                    <span class="calendar-status" id="calendarStatus">Loading…</span>
                    <button type="button" class="btn btn-small btn-secondary" id="btnRefresh" aria-label="Refresh">Refresh</button>
                </div>
            </section>

            <section class="queue-section">
                <h2>Queue / Timeline</h2>
                <div class="queue-legend">
                    <span class="legend-item legend-pending">Pending</span>
                    <span class="legend-item legend-inprogress">In progress</span>
                    <span class="legend-item legend-completed">Completed</span>
                </div>
                <div class="queue-list" id="queueList">
                    <p class="empty-state" id="queueEmpty">No scheduled orders.</p>
                </div>
            </section>

            <section class="utilization-section">
                <h2>Equipment utilization</h2>
                <div class="utilization-grid" id="utilizationGrid">
                    <p class="empty-state" id="utilEmpty">No equipment data.</p>
                </div>
            </section>
        </main>
    </div>

    <div class="modal-overlay" id="editModal" aria-hidden="true">
        <div class="modal modal-wide" role="dialog" aria-labelledby="editModalTitle">
            <h2 id="editModalTitle">Edit order</h2>
            <input type="hidden" id="editQueueId" name="queue_id">
            <form id="editForm">
                <div class="form-group">
                    <label for="editStart">Scheduled start</label>
                    <input type="datetime-local" id="editStart" name="scheduled_start" required>
                </div>
                <div class="form-group">
                    <label for="editEnd">Scheduled end</label>
                    <input type="datetime-local" id="editEnd" name="scheduled_end" required>
                </div>
                <div class="form-group">
                    <label for="editMessage">Optional note to customer (for schedule change email)</label>
                    <textarea id="editMessage" name="message" rows="2" class="form-control" placeholder="Optional note about this schedule change"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="btnCancelEdit">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save schedule</button>
                </div>
            </form>
            <div class="edit-modal-divider"></div>
            <div class="edit-modal-finish-section">
                <h3 class="edit-modal-finish-title">Mark as completed</h3>
                <p class="edit-modal-finish-desc">Send the customer a completion email (optional note and attachment).</p>
                <div class="form-group">
                    <label for="finishMessageInEdit">Optional note (default: order analysis is done)</label>
                    <textarea id="finishMessageInEdit" rows="3" class="form-control" placeholder="Optional note for completion email"></textarea>
                </div>
                <div class="form-group">
                    <label for="finishAttachmentInEdit">Attach file (e.g. results PDF)</label>
                    <input type="file" id="finishAttachmentInEdit" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,image/*">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" id="btnFinishOrderInEdit">Finish order</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="js/calendar.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>
