<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <a href="dashboard.php"><?php echo APP_NAME; ?></a>
        </div>

        <?php
        // Determine current page and tab for active state
        $currentPage = basename($_SERVER['PHP_SELF']);
        $currentTab = isset($_GET['tab']) ? $_GET['tab'] : '';
        ?>

        <nav class="main-nav">
            <?php if (isset($_SESSION['user_role'])): ?>
                <?php if ($_SESSION['user_role'] === 'customer'): ?>
                    <a href="dashboard.php"
                        class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="my-orders.php" class="<?php echo $currentPage === 'my-orders.php' ? 'active' : ''; ?>">My
                        Orders</a>
                    <a href="create-order.php" class="<?php echo $currentPage === 'create-order.php' ? 'active' : ''; ?>">New
                        Order</a>
                    <a href="create-event.php" class="<?php echo $currentPage === 'create-event.php' ? 'active' : ''; ?>">Event
                        Scheduler</a>
                    <a href="history-order-Cus.php"
                        class="<?php echo $currentPage === 'history-order-Cus.php' ? 'active' : ''; ?>">Order History</a>

                <?php elseif ($_SESSION['user_role'] === 'technician'): ?>
                    <a href="dashboard.php"
                        class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="tech-samples.php"
                        class="<?php echo $currentPage === 'tech-samples.php' ? 'active' : ''; ?>">Samples</a>
                    <a href="tech-equipment.php"
                        class="<?php echo $currentPage === 'tech-equipment.php' ? 'active' : ''; ?>">Equipment</a>
                    <a href="tech-queue.php"
                        class="<?php echo $currentPage === 'tech-queue.php' ? 'active' : ''; ?>">Queue</a>
                    <a href="history-order-tech.php"
                        class="<?php echo $currentPage === 'history-order-tech.php' ? 'active' : ''; ?>">Order History</a>

                <?php elseif ($_SESSION['user_role'] === 'administrator'): ?>
                    <a href="dashboard.php"
                        class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="admin.php?tab=approvals"
                        class="<?php echo $currentPage === 'admin.php' && $currentTab === 'approvals' ? 'active' : ''; ?>">Approvals</a>
                    <a href="admin.php?tab=users"
                        class="<?php echo $currentPage === 'admin.php' && $currentTab === 'users' ? 'active' : ''; ?>">Users</a>
                    <a href="admin.php?tab=equipment"
                        class="<?php echo $currentPage === 'admin.php' && $currentTab === 'equipment' ? 'active' : ''; ?>">Equipment</a>
                    <a href="admin.php?tab=reports"
                        class="<?php echo $currentPage === 'admin.php' && $currentTab === 'reports' ? 'active' : ''; ?>">Reports</a>
                    <a href="history-order-Adm.php?tab=order history"
                        class="<?php echo $currentPage === 'history-order-Adm.php' && $currentTab === 'order history' ? 'active' : ''; ?>">Order
                        History</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <div class="user-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['user_name'])): ?>
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-small">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>