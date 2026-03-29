<?php
declare(strict_types=1);

$__adminTitle = 'Users';
require __DIR__ . '/_init.php';

$usersQueryParams = array_filter([
    'user_search' => $userSearch,
    'user_role' => $userRoleFilter,
    'user_status' => $userStatusFilter,
], static fn ($v) => $v !== '');
$usersActionSuffix = $usersQueryParams !== [] ? '?' . http_build_query($usersQueryParams) : '';

include __DIR__ . '/_html_start.php';
?>
                <section class="admin-section">
                    <h1>Manage Users</h1>
                    <p class="section-desc">Create, modify, and manage user accounts and permissions.</p>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form method="get" action="<?php echo htmlspecialchars(app_path('admin/users.php'), ENT_QUOTES, 'UTF-8'); ?>" class="filter-bar">
                        <input type="text" name="user_search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($userSearch); ?>">
                        <select name="user_role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="customer" <?php echo $userRoleFilter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                            <option value="technician" <?php echo $userRoleFilter === 'technician' ? 'selected' : ''; ?>>Technician</option>
                            <option value="administrator" <?php echo $userRoleFilter === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                        <select name="user_status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $userStatusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $userStatusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </form>

                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usersList)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No users found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($usersList as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo htmlspecialchars($u['company_name'] ?? '—'); ?></td>
                                        <td>
                                            <form method="post" action="<?php echo htmlspecialchars(app_path('admin/users.php') . $usersActionSuffix, ENT_QUOTES, 'UTF-8'); ?>" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>">
                                                <select name="role" class="form-control" style="width:auto; display:inline-block; padding:6px 8px;" onchange="this.form.submit()">
                                                    <option value="customer" <?php echo $u['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                    <option value="technician" <?php echo $u['role'] === 'technician' ? 'selected' : ''; ?>>Technician</option>
                                                    <option value="administrator" <?php echo $u['role'] === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                                                </select>
                                                <input type="hidden" name="change_role" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo !empty($u['is_active']) ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo !empty($u['is_active']) ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $u['last_login'] ? date('Y-m-d H:i', strtotime($u['last_login'])) : '—'; ?></td>
                                        <td class="actions">—</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
<?php include __DIR__ . '/_html_end.php'; ?>
    <script type="module" src="frontend/src/pages/admin/adminShared.js"></script>
</body>
</html>
