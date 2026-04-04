<script>
/* Immediately apply saved theme before first paint — prevents flash */
(function(){
    try {
        var t = localStorage.getItem('theme');
        if (t) {
            document.documentElement.setAttribute('data-theme', t);
        }
    } catch (e) {
        // If storage is unavailable, keep default light theme.
    }
})();
</script>
<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <a href="dashboard.php"><?php echo APP_NAME; ?></a>
        </div>

        <button
            type="button"
            class="nav-toggle"
            id="siteNavToggle"
            data-menu-toggle
            data-menu-target="siteNavPanel"
            data-menu-active-class="is-open"
            data-menu-body-class="site-nav-open"
            aria-expanded="false"
            aria-controls="siteNavPanel"
            aria-label="Toggle navigation"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <?php
        // Determine current page and tab for active state
        $currentPage = basename($_SERVER['PHP_SELF']);
        $currentTab = isset($_GET['tab']) ? $_GET['tab'] : '';
        ?>

<<<<<<< Updated upstream
        <nav class="main-nav">
            <?php if (isset($_SESSION['user_role'])): ?>
                <?php if ($_SESSION['user_role'] === 'customer'): ?>
                    <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="my-orders.php" class="<?php echo $currentPage === 'my-orders.php' ? 'active' : ''; ?>">My Orders</a>
                    <a href="order-history.php" class="<?php echo $currentPage === 'order-history.php' ? 'active' : ''; ?>">Order History</a>
                    <a href="create-order.php" class="<?php echo $currentPage === 'create-order.php' ? 'active' : ''; ?>">New Order</a>
                    <a href="contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact Us</a>
                <?php elseif ($_SESSION['user_role'] === 'technician'): ?>
                    <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="#">Samples</a>
                    <a href="#">Equipment</a>
                    <a href="calendar.php" class="<?php echo $currentPage === 'calendar.php' ? 'active' : ''; ?>">Calendar</a>
                    <a href="order-history.php" class="<?php echo $currentPage === 'order-history.php' ? 'active' : ''; ?>">Order History</a>
                <?php elseif ($_SESSION['user_role'] === 'administrator'): ?>
                    <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="admin.php?tab=approvals" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'approvals' ? 'active' : ''; ?>">Approvals</a>
                    <a href="calendar.php" class="<?php echo $currentPage === 'calendar.php' ? 'active' : ''; ?>">Calendar</a>
                    <a href="order-history.php" class="<?php echo $currentPage === 'order-history.php' ? 'active' : ''; ?>">Order History</a>
                    <a href="admin.php?tab=users" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'users' ? 'active' : ''; ?>">Users</a>
                    <a href="admin.php?tab=equipment" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'equipment' ? 'active' : ''; ?>">Equipment</a>
                    <a href="admin.php?tab=reports" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'reports' ? 'active' : ''; ?>">Reports</a>
=======
        <div class="header-nav-panel" id="siteNavPanel" data-menu-panel>
            <nav class="main-nav">
                <?php if (isset($_SESSION['user_role'])): ?>
                    <?php if ($_SESSION['user_role'] === 'customer'): ?>
                        <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                        <a href="my-orders.php" class="<?php echo $currentPage === 'my-orders.php' ? 'active' : ''; ?>">My Orders</a>
                        <a href="order-history.php" class="<?php echo $currentPage === 'order-history.php' ? 'active' : ''; ?>">Order History</a>
                        <a href="create-order.php" class="<?php echo $currentPage === 'create-order.php' ? 'active' : ''; ?>">New Order</a>
                        <a href="contact.php" class="<?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact Us</a>
                    <?php elseif ($_SESSION['user_role'] === 'technician'): ?>
                        <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                        <a href="admin.php?tab=approvals" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'approvals' ? 'active' : ''; ?>">Approvals</a>
                        <a href="calendar.php" class="<?php echo $currentPage === 'calendar.php' ? 'active' : ''; ?>">Calendar</a>
                    <?php elseif ($_SESSION['user_role'] === 'administrator'): ?>
                        <a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                        <a href="admin.php?tab=approvals" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'approvals' ? 'active' : ''; ?>">Approvals</a>
                        <a href="calendar.php" class="<?php echo $currentPage === 'calendar.php' ? 'active' : ''; ?>">Calendar</a>
                        <a href="order-history.php" class="<?php echo $currentPage === 'order-history.php' ? 'active' : ''; ?>">Order History</a>
                        <a href="admin.php?tab=users" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'users' ? 'active' : ''; ?>">Users</a>
                        <a href="admin.php?tab=equipment" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'equipment' ? 'active' : ''; ?>">Equipment</a>
                        <a href="admin.php?tab=reports" class="<?php echo $currentPage === 'admin.php' && $currentTab === 'reports' ? 'active' : ''; ?>">Reports</a>
                    <?php endif; ?>
>>>>>>> Stashed changes
                <?php endif; ?>
            </nav>

            <div class="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <?php endif; ?>
                    <a href="account-settings.php" class="btn btn-small btn-secondary header-action-btn header-account-btn">Account</a>
                    <a href="logout.php" class="btn btn-small btn-secondary header-action-btn logout-btn">Logout</a>
                <?php endif; ?>
<<<<<<< Updated upstream
                <a href="logout.php" class="btn btn-small">Logout</a>
            <?php endif; ?>
=======
            </div>
>>>>>>> Stashed changes
        </div>
    </div>
</header>
