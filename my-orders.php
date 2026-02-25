<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Order.php';

$user = new User();

// Check if user is logged in and is customer
if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Initialize Order class
$order = new Order();

// Get customer's orders
$orders = $order->getOrdersByCustomer($userId);

// Count orders by status
$statusCounts = [
    'submitted' => 0,
    'approved' => 0,
    'processing' => 0,
    'completed' => 0,
    'rejected' => 0
];

foreach ($orders as $o) {
    if (isset($statusCounts[$o['status']])) {
        $statusCounts[$o['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pO1P5TixY9gSBchiS+OgGnl78rk+Lv9De/0EaP0Z5d+twT0FhbhLukItJNclvYLIT5Q1eI3xg5RjyV9O6LDD1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* let the global stylesheet control body background (blue gradient) */
        .orders-container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 20px 15px;
            background: transparent; /* let body color show through */
        }
        .orders-header {
            background: white;
            border-radius: 10px;
            padding: 25px 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .orders-header h1 {
            color: #333;
            margin-bottom: 5px;
            font-size: 26px;
            font-weight: 600;
        }
        .orders-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        .orders-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            align-items: flex-start;
        }
        .status-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            flex: 1 1 400px;
        }
        .status-card {
            background: white;
            border-radius: 10px;
            padding: 20px 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .status-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .status-card.active {
            outline: 2px solid #667eea;
            outline-offset: -2px;
            box-shadow: 0 4px 20px rgba(102,126,234,0.4);
            background: #eef3ff;
        }
        .status-card i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #667eea;
        }
        .status-card .count {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .status-card .label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .status-card.pending .count,
        .status-card.pending i { color: #ffc107; }
        .status-card.approved .count,
        .status-card.approved i { color: #17a2b8; }
        .status-card.processing .count,
        .status-card.processing i { color: #667eea; }
        .status-card.completed .count,
        .status-card.completed i { color: #28a745; }
        .status-card.rejected .count,
        .status-card.rejected i { color: #dc3545; }
        .calendar-card {
            flex: 1 1 500px;
        }
        .orders-table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .orders-table-container h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .clear-filter {
            margin-top: 10px;
            font-size: 13px;
        }
        .clear-filter a {
            color: #667eea;
            text-decoration: none;
        }
        .clear-filter a:hover {
            text-decoration: underline;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-submitted { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #e7e3ff; color: #5a4fcf; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-orders h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .empty-orders p {
            margin-bottom: 20px;
        }
        .new-order-btn {
            display: inline-block;
            margin-top: 15px;
        }
        /* calendar styling */
        #calendar { max-width:100%; margin:20px auto 20px; background:#f9fafb; padding:15px; border:1px solid #ddd; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,.05); min-height:400px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">


    <div class="orders-container">
        <div class="orders-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>My Orders</h1>
                <p>View and track all your submitted orders</p>
            </div>
            <div>
            <a href="create-order.php" class="btn btn-primary btn-small">Create</a>
            </div>
        </div>

        <div class="orders-summary">
            <div class="status-cards">
                <div class="status-card pending" data-status="submitted">
                    <i class="fas fa-hourglass-start"></i>
                    <div class="count"><?php echo $statusCounts['submitted']; ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="status-card approved" data-status="approved">
                    <i class="fas fa-check-circle"></i>
                    <div class="count"><?php echo $statusCounts['approved']; ?></div>
                    <div class="label">Approved</div>
                </div>
                <div class="status-card processing" data-status="processing">
                    <i class="fas fa-sync-alt"></i>
                    <div class="count"><?php echo $statusCounts['processing']; ?></div>
                    <div class="label">Processing</div>
                </div>
                <div class="status-card completed" data-status="completed">
                    <i class="fas fa-check-double"></i>
                    <div class="count"><?php echo $statusCounts['completed']; ?></div>
                    <div class="label">Completed</div>
                </div>
                <div class="status-card rejected" data-status="rejected">
                    <i class="fas fa-times-circle"></i>
                    <div class="count"><?php echo $statusCounts['rejected']; ?></div>
                    <div class="label">Rejected</div>
                </div>
            </div>
            <div id="clearFilter" class="clear-filter" style="display:none;"><a href="#" onclick="clearFilter(); return false;">Clear filter</a></div>

            <!-- calendar card added for customer -->
            <div class="calendar-card">
                <h2>Order Calendar</h2>
                <div id="calendar"></div>
            </div>
        </div>

        <div class="orders-table-container">
            <h2>Order History</h2>

            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <h3>No Orders Yet</h3>
                    <p>You haven't submitted any orders. Start by creating a new order.</p>
                    <a href="create-order.php" class="btn btn-primary new-order-btn">Create New Order</a>
                </div>
            <?php else: ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Submitted</th>
                                <th>Priority</th>
                                <th>Samples</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($o['created_at'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $o['priority']; ?>">
                                        <?php echo ucfirst($o['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo $o['sample_count']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $o['status']; ?>">
                                        <?php echo ucfirst($o['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-small btn-secondary"><i class="fas fa-eye"></i> View Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
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
            events: function(fetchInfo, success, failure) {
                fetch('get_calendar_events.php')
                    .then(r => r.json())
                    .then(data => success((data && data.length) ? data : []))
                    .catch(e => { console.error(e); failure(e); });
            },
            eventColor: '#667eea',
            eventTextColor: '#fff',
            eventDisplay: 'block',
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
    <script>
    // card-based filtering with clear link (no search box)
    document.addEventListener('DOMContentLoaded', function() {
        var rows = document.querySelectorAll('.admin-table tbody tr');
        var cards = document.querySelectorAll('.status-card');
        var clearLink = document.getElementById('clearFilter');
        var activeStatus = '';
        function updateVisibility(showAll) {
            rows.forEach(function(row) {
                if (showAll) {
                    row.style.display = '';
                } else {
                    var rowStatus = row.querySelector('.status-badge').textContent.toLowerCase();
                    row.style.display = rowStatus === activeStatus ? '' : 'none';
                }
            });
        }
        cards.forEach(function(card) {
            card.addEventListener('click', function() {
                var status = card.getAttribute('data-status');
                var isActive = card.classList.contains('active');
                cards.forEach(c => c.classList.remove('active'));
                clearLink.style.display = 'none';
                if (!isActive) {
                    card.classList.add('active');
                    activeStatus = status;
                    clearLink.style.display = 'block';
                    updateVisibility(false);
                } else {
                    updateVisibility(true);
                }
            });
        });
        window.clearFilter = function() {
            cards.forEach(c => c.classList.remove('active'));
            updateVisibility(true);
            clearLink.style.display = 'none';
        };
    });
    </script>
    <script src="js/main.js"></script>
</body>
</html>
