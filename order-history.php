<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$role = $user->getRole();

// technicians are allowed to view calendar; they don't see tables/search if not needed
// (role-based filtering happens later in the HTML)

$order = new Order();
$searchOrderNumber = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';
$searchDateFrom   = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$searchDateTo     = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$searchCustomerName = isset($_GET['customer_name']) ? trim($_GET['customer_name']) : '';
// status filter array from query
$searchStatuses = isset($_GET['status']) ? (array)$_GET['status'] : [];

// fetch orders using general helper (applies filters and status)
$orders = $order->getOrdersForRole($role, $_SESSION['user_id'], $searchOrderNumber, $searchDateFrom, $searchDateTo, $searchStatuses);
// split by priority for table display
$standardOrders = array_filter($orders, function ($o) { return ($o['priority'] ?? '') === 'standard'; });
$prioritizedOrders = array_filter($orders, function ($o) { return ($o['priority'] ?? '') === 'prioritized'; });
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - <?php echo APP_NAME; ?></title>
<<<<<<< Updated upstream
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <style>
        .orders-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        /* sidebar fixed width */
        .sidebar { flex: 0 0 300px; max-width: 300px; }

        /* calendar styling */
        #calendar { max-width:900px; margin:20px auto 40px; background:#f9fafb; padding:15px; border:1px solid #ddd; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,.05); min-height:400px; }
        .orders-header { text-align: center; margin-bottom: 20px; }
        .orders-header h1 { color: #333; margin-bottom: 5px; }
        .orders-header p { color: #666; margin: 0; }
        .search-form { background: white; border-radius: 10px; padding: 20px 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-form form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .sidebar .search-form form { flex-direction: column; align-items: stretch; }
        .sidebar .search-form form > div { width: 100%; }
        .search-form label { display: block; font-size: 12px; color: #666; margin-bottom: 4px; }
        .search-form input[type="text"], .search-form input[type="date"] { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 100%; box-sizing: border-box; }
        .search-form button { padding: 8px 20px; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .search-form button[type="reset"] { background: #6c757d; }
        .search-form .status-group { display:flex; flex-wrap: wrap; gap:8px; max-width:100%; overflow-x:auto; }
        .search-form .status-group label { font-size:12px; white-space:nowrap; flex-shrink:0; }

        .search-form .status-group label { font-size:12px; white-space:nowrap; }
        @media (max-width:600px) {
            .search-form form { flex-direction: column; align-items: stretch; }
            .search-form input[type="text"], .search-form input[type="date"] { width:100%; }
            .search-form .status-group { justify-content:flex-start; }
        }
        /* new layout rules */
        .layout { display: flex; gap: 20px; align-items: flex-start; flex-wrap: nowrap; }
        .sidebar { flex: 0 0 300px; background: #fff; border-radius:10px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }


        .main-content { flex: 1; }
        @media (max-width:800px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; }
        }
        .history-section { background: white; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .history-section h2 { color: #333; margin-bottom: 15px; font-size: 18px; }
=======
    <link rel="stylesheet" href="css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo ASSET_VERSION; ?>">
    <style>
        .orders-container { max-width: 1200px; margin: 0 auto; padding: 20px; width: 100%; }
        .orders-header { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 25px 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .orders-header h1 { color: var(--text-primary); margin-bottom: 5px; }
        .orders-header p { color: var(--text-secondary); margin: 0; }
        .search-form { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .search-form {
            overflow: visible;
        }
        .search-form form {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            align-items: end;
            width: 100%;
        }
        .search-form form.search-form-customer {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .search-form form > * {
            min-width: 0;
        }
        .search-field { min-width: 0; }
        .search-actions {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            width: 100%;
        }
        .search-form label { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 4px; }
        .search-form input[type="text"], .search-form input[type="date"] { width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); background: var(--bg-elevated); color: var(--text-primary); border-radius: 6px; min-width: 0; }
        .search-form button { width: 100%; padding: 8px 20px; background: linear-gradient(135deg, var(--btn-grad-start, #5f72ff) 0%, var(--btn-grad-end, #9b23ea) 100%); color: white; border: none; border-radius: 6px; cursor: pointer; }
        .search-form button[type="reset"] { background: linear-gradient(135deg, var(--btn-grad-start, #5f72ff) 0%, var(--btn-grad-end, #9b23ea) 100%); }
        .history-section { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .history-section h2 { color: var(--text-primary); margin-bottom: 15px; font-size: 18px; }
>>>>>>> Stashed changes
        .admin-table-container { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .admin-table th { background: var(--bg-elevated); font-weight: 600; color: var(--text-primary); }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-results_available, .status-completed { background: #d4edda; color: #155724; }
        .badge-standard { background: #e9ecef; color: #495057; }
        .badge-prioritized { background: #fff3cd; color: #856404; }
<<<<<<< Updated upstream
        .empty-history { text-align: center; padding: 40px 20px; color: #666; }
        /* calendar box styling */
        .calendar-card { max-width:900px; margin:0 auto 20px; background:#f9fafb; padding:15px; border:1px solid #ddd; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,.05); min-height:400px; }
        .calendar-card h2 { text-align:center; margin:0 0 10px; }
        .sidebar h2, .calendar-card h2 { font-size:18px; color:#333; margin-bottom:15px; }
=======
        .empty-history { text-align: center; padding: 40px 20px; color: var(--text-secondary); }
        @media (max-width: 992px) {
            .search-form form,
            .search-form form.search-form-customer { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 768px) {
            .orders-container { padding: 12px 14px 16px; }
            .orders-header, .search-form, .history-section { padding: 18px 14px; }
            .search-form form { align-items: stretch; grid-template-columns: 1fr; }
            .search-actions { grid-template-columns: 1fr; }
            .search-actions button { width: 100%; }
        }
>>>>>>> Stashed changes
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="orders-container">
        <div class="orders-header">
            <h1><?php echo $role === 'technician' ? 'Order Calendar' : 'Order History & Calendar'; ?></h1>
            <?php if ($role === 'customer'): ?>
                <p>Your completed orders</p>
            <?php elseif ($role === 'administrator'): ?>
                <p>All completed orders</p>
            <?php else: ?>
                <p>View order schedule and key dates</p>
            <?php endif; ?>
        </div>

        <div class="layout">
            <aside class="sidebar">
        <div class="search-form">
<<<<<<< Updated upstream
            <h2>Filters</h2>
            <form method="get" action="order-history.php">
=======
            <form method="get" action="order-history.php" class="<?php echo $role === 'customer' ? 'search-form-customer' : ''; ?>">
>>>>>>> Stashed changes
                <?php if ($role === 'administrator'): ?>
                    <div class="search-field">
                        <label>Customer name</label>
                        <input type="text" name="customer_name" value="<?php echo htmlspecialchars($searchCustomerName); ?>" placeholder="Name or email">
                    </div>
                <?php endif; ?>
                <div class="search-field">
                    <label>Order number</label>
                    <input type="text" name="order_number" value="<?php echo htmlspecialchars($searchOrderNumber); ?>" placeholder="e.g. ORD-">
                </div>
                <div class="search-field">
                    <label>From date</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($searchDateFrom); ?>">
                </div>
                <div class="search-field">
                    <label>To date</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($searchDateTo); ?>">
                </div>
<<<<<<< Updated upstream
                <div>
                    <label>Status</label>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <?php
                        $allStatuses = ['completed','submitted','approved','in_queue','rejected','pending_approval','payment_pending','payment_confirmed','preparation_in_progress','testing_in_progress','results_available'];
                        foreach ($allStatuses as $s): ?>
                            <label style="font-size:12px;">
                                <input type="checkbox" name="status[]" value="<?= $s ?>" <?= in_array($s, $searchStatuses) ? 'checked' : '' ?>>
                                <?= htmlspecialchars(str_replace('_', ' ', ucfirst($s))) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
=======
                <div class="search-actions">
>>>>>>> Stashed changes
                    <button type="submit">Search</button>
                    <button type="reset" onclick="window.location='order-history.php';">Clear</button>
                </div>
            </form>
        </div>
            </aside>
            <div class="main-content">

        <!-- calendar container -->
        <div class="calendar-card">
            <h2>Order Calendar</h2>
            <div id="calendar"></div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="history-section">
                <div class="empty-history">
                    <h3>No completed orders found</h3>
                    <?php if($role !== 'technician'): ?>
                        <p><?php echo $role === 'customer' ? 'You have no finished orders yet, or no orders match your search.' : 'No finished orders match your search.'; ?></p>
                    <?php else: ?>
                        <p>There are currently no orders to display on the calendar.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php if ($role !== 'technician' && !empty($prioritizedOrders)): ?>
            <div class="history-section">
                <h2>Prioritized orders</h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <?php if ($role === 'administrator'): ?><th>Customer</th><?php endif; ?>
                                <th>Completed / Date</th>
                                <th>Samples</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prioritizedOrders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                                <?php if ($role === 'administrator'): ?>
                                    <td><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                                <?php endif; ?>
                                <td><?php echo $o['completed_at'] ? date('M d, Y H:i', strtotime($o['completed_at'])) : (isset($o['estimated_completion']) && $o['estimated_completion'] ? date('M d, Y', strtotime($o['estimated_completion'])) : '-'); ?></td>
                                <td><?php echo (int)($o['sample_count'] ?? 0); ?></td>
                                <td><span class="status-badge status-<?php echo $o['status'] ?? 'completed'; ?>"><?php echo ucfirst(str_replace('_', ' ', $o['status'] ?? 'Completed')); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($role !== 'technician' && !empty($standardOrders)): ?>
            <div class="history-section">
                <h2>Standard orders</h2>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <?php if ($role === 'administrator'): ?><th>Customer</th><?php endif; ?>
                                <th>Completed / Date</th>
                                <th>Samples</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($standardOrders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                                <?php if ($role === 'administrator'): ?>
                                    <td><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                                <?php endif; ?>
                                <td><?php echo $o['completed_at'] ? date('M d, Y H:i', strtotime($o['completed_at'])) : (isset($o['estimated_completion']) && $o['estimated_completion'] ? date('M d, Y', strtotime($o['estimated_completion'])) : '-'); ?></td>
                                <td><?php echo (int)($o['sample_count'] ?? 0); ?></td>
                                <td><span class="status-badge status-<?php echo $o['status'] ?? 'completed'; ?>"><?php echo ucfirst(str_replace('_', ' ', $o['status'] ?? 'Completed')); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
            </div> <!-- end main-content -->
        </div> <!-- end layout -->
    </div>

    <?php include 'includes/footer.php'; ?>
<<<<<<< Updated upstream
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            // fetch events, including any current query parameters (filters)
            events: function(fetchInfo, success, failure) {
                var query = window.location.search.substring(1);
                var url = 'get_calendar_events.php' + (query ? '?' + query : '');
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        console.log('calendar events', data);
                        // if there are no events, just pass empty array; the grid will still render
                        success((data && data.length) ? data : []);
                    })
                    .catch(e => { console.error(e); failure(e); });
            },
            eventColor: '#667eea',
            eventTextColor: '#fff',
            eventDisplay: 'block',
            // give a fixed height so the grid is visible even with no events
            height: 700,
            navLinks: true,
            editable: false,
            dayMaxEvents: true,
            eventDidMount: function(info) {
                info.el.setAttribute('title', info.event.extendedProps.description);
            },
            noEventsContent: 'No orders scheduled',
        });
        calendar.render();
    });
    </script>
    <script src="js/main.js"></script>
=======
    <script src="js/main.js?v=<?php echo ASSET_VERSION; ?>"></script>
>>>>>>> Stashed changes
</body>
</html>
