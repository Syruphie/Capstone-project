<?php
declare(strict_types=1);

if (!function_exists('app_path')) {
    require_once dirname(__DIR__, 3) . '/includes/app_paths.php';
}

$script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$currentRoute = '';
if (preg_match('#/public/pages/(.+)$#', $script, $m)) {
    $currentRoute = $m[1];
}
?>
<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <a href="<?php echo htmlspecialchars(app_path('dashboard/index.php'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo APP_NAME; ?></a>
        </div>

        <nav class="main-nav">
            <?php if (isset($_SESSION['user_role'])): ?>
                <?php if ($_SESSION['user_role'] === 'customer'): ?>
                    <a href="<?php echo htmlspecialchars(app_path('dashboard/index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'dashboard/index.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="<?php echo htmlspecialchars(app_path('orders/my-orders.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'orders/my-orders.php' ? 'active' : ''; ?>">My Orders</a>
                    <a href="<?php echo htmlspecialchars(app_path('orders/order-history.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'orders/order-history.php' ? 'active' : ''; ?>">Order History</a>
                    <a href="<?php echo htmlspecialchars(app_path('orders/create-order.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'orders/create-order.php' ? 'active' : ''; ?>">New Order</a>
                    <a href="<?php echo htmlspecialchars(app_path('contact/index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'contact/index.php' ? 'active' : ''; ?>">Contact Us</a>
                <?php elseif ($_SESSION['user_role'] === 'technician'): ?>
                    <a href="<?php echo htmlspecialchars(app_path('dashboard/index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'dashboard/index.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="<?php echo htmlspecialchars(app_path('admin/approvals.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'admin/approvals.php' ? 'active' : ''; ?>">Approvals</a>
                    <a href="<?php echo htmlspecialchars(app_path('calendar/index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'calendar/index.php' ? 'active' : ''; ?>">Calendar</a>
                <?php elseif ($_SESSION['user_role'] === 'administrator'): ?>
                    <a href="<?php echo htmlspecialchars(app_path('dashboard/index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'dashboard/index.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="<?php echo htmlspecialchars(app_path('admin/approvals.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'admin/approvals.php' ? 'active' : ''; ?>">Approvals</a>
                    <a href="<?php echo htmlspecialchars(app_path('calendar/index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'calendar/index.php' ? 'active' : ''; ?>">Calendar</a>
                    <a href="<?php echo htmlspecialchars(app_path('orders/order-history.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'orders/order-history.php' ? 'active' : ''; ?>">Order History</a>
                    <a href="<?php echo htmlspecialchars(app_path('admin/users.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'admin/users.php' ? 'active' : ''; ?>">Users</a>
                    <a href="<?php echo htmlspecialchars(app_path('admin/equipment.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'admin/equipment.php' ? 'active' : ''; ?>">Equipment</a>
                    <a href="<?php echo htmlspecialchars(app_path('admin/reports.php'), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $currentRoute === 'admin/reports.php' ? 'active' : ''; ?>">Reports</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="user-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars(app_path('account/settings.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-small btn-account <?php echo $currentRoute === 'account/settings.php' ? 'active' : ''; ?>" style="margin-right:8px;">Account</a>
                <a href="<?php echo htmlspecialchars(app_path('auth/logout.php'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-small">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- ================= CHATBOT START ================= -->

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<div id="chatbot-button">
    <span class="material-icons">chat</span>
</div>

<div id="chatbot-window">
    <div id="chatbot-header">
        <span>Virtual Assistant</span>
        <span id="chatbot-close">&times;</span>
    </div>

    <div id="chatbot-messages">
        <div id="quick-questions">
            <div class="question">How do I approve orders?</div>
            <div class="question">Where can I manage users?</div>
            <div class="question">How do I manage equipment?</div>
            <div class="question">Where can I view reports?</div>
            <div class="question">How do I logout?</div>
        </div>
    </div>

    <div id="chatbot-input-area">
        <input type="text" id="chatbot-input" placeholder="Ask me something...">
        <button id="chatbot-send">Send</button>
    </div>
</div>

<link rel="stylesheet" href="css/chat.css">
<script type="module" src="frontend/src/components/chatbot/chatbotWidget.js"></script>

<!-- ================= CHATBOT END ================= -->
