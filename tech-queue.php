<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Equipment.php';

$user = new User();

if (!$user->isLoggedIn() || $user->getRole() !== 'technician') {
    header('Location: login.php');
    exit;
}

$equipmentObj = new Equipment();
$equipmentList = $equipmentObj->getAllEquipment();

$totalEquipment = count($equipmentList);
$availableCount = 0;
$unavailableCount = 0;

foreach ($equipmentList as $eq) {
    if (!empty($eq['is_available'])) $availableCount++;
    else $unavailableCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        :root {
            --text: #0f172a;
            --muted: #64748b;
            --border: rgba(15, 23, 42, .10);
        }

        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }

        .welcome-section, .dashboard-card, .table-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
        }

        .welcome-section {
            padding: 32px;
            margin-bottom: 28px;
        }

        .welcome-section h1 {
            font-size: 42px;
            font-weight: 900;
            margin: 0 0 10px;
            color: var(--text);
        }

        .welcome-section p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
            font-weight: 600;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 26px;
            margin-bottom: 26px;
        }

        .dashboard-card {
            padding: 28px;
        }

        .dashboard-card h2 {
            font-size: 22px;
            font-weight: 900;
            margin: 0 0 10px;
            color: var(--text);
        }

        .dashboard-card p {
            margin: 0 0 18px;
            color: var(--muted);
        }

        .stat {
            font-size: 28px;
            font-weight: 900;
            color: #5b4ae6;
        }

        .table-card {
            padding: 28px;
        }

        .table-card h2 {
            margin: 0 0 14px;
            font-size: 24px;
            font-weight: 900;
            color: var(--text);
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .dashboard-table th {
            text-align: left;
            padding: 14px;
            font-weight: 900;
            border-bottom: 1px solid var(--border);
            background: #fafafa;
            color: var(--muted);
        }

        .dashboard-table td {
            padding: 16px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        .dashboard-table tr:hover {
            background: rgba(91, 74, 230, .05);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 0;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-unavailable {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            font-weight: 700;
            padding: 20px !important;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Equipment</h1>
            <p>Monitor laboratory equipment availability and settings.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Total Equipment</h2>
                <p>All equipment currently configured.</p>
                <div class="stat"><?php echo $totalEquipment; ?></div>
            </div>

            <div class="dashboard-card">
                <h2>Available</h2>
                <p>Equipment ready for technician use.</p>
                <div class="stat"><?php echo $availableCount; ?></div>
            </div>

            <div class="dashboard-card">
                <h2>Unavailable</h2>
                <p>Equipment currently unavailable.</p>
                <div class="stat"><?php echo $unavailableCount; ?></div>
            </div>
        </div>

        <div class="table-card">
            <h2>Equipment List</h2>

            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Processing Time</th>
                        <th>Warmup</th>
                        <th>Break Interval</th>
                        <th>Daily Capacity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equipmentList)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">No equipment found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipmentList as $eq): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($eq['name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($eq['equipment_type'] ?? '-'); ?></td>
                                <td><?php echo (int)($eq['processing_time_per_sample'] ?? 0); ?> min/sample</td>
                                <td><?php echo (int)($eq['warmup_time'] ?? 0); ?> min</td>
                                <td><?php echo (int)($eq['break_interval'] ?? 0); ?> samples</td>
                                <td><?php echo (int)($eq['daily_capacity'] ?? 0); ?> samples</td>
                                <td>
                                    <span class="status-badge <?php echo !empty($eq['is_available']) ? 'status-available' : 'status-unavailable'; ?>">
                                        <?php echo !empty($eq['is_available']) ? 'Available' : 'Unavailable'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>