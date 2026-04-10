<?php
require_once __DIR__ . '/../bootstrap_paths.php';

$user = new FrontendUser();
if (!$user->isLoggedIn()) {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $user->getRole();
$profile = $user->getUserById($userId);
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
        ];
        if (empty($data['full_name'])) {
            $error = 'Full name is required.';
        } elseif ($user->updateUser($userId, $data)) {
            $message = 'Profile updated successfully.';
            $_SESSION['user_name'] = $data['full_name'];
            $profile = $user->getUserById($userId);
        } else {
            $error = 'Failed to update profile.';
        }
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($newPass) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($newPass !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif ($user->changePassword($userId, $current, $newPass)) {
            $message = 'Password changed successfully.';
        } else {
            $error = 'Current password is incorrect or change failed.';
        }
    } elseif (isset($_POST['deactivate_self'])) {
        if ($user->deactivateUser($userId)) {
            session_destroy();
            header('Location: ' . app_path('auth/login.php') . '?deactivated=1');
            exit;
        }
        $error = 'Could not deactivate account.';
    } elseif (isset($_POST['change_role']) && $userRole === 'administrator') {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        $newRole = trim($_POST['role'] ?? '');
        if ($targetId && in_array($newRole, ['customer', 'technician', 'administrator'], true)) {
            if ($user->assignRole($targetId, $newRole)) {
                $message = 'User role updated.';
            } else {
                $error = 'Failed to update role.';
            }
        }
    } elseif (isset($_POST['deactivate_user']) && $userRole === 'administrator') {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        if ($targetId && $targetId != $userId) {
            if ($user->deactivateUser($targetId)) {
                $message = 'User deactivated.';
            } else {
                $error = 'Failed to deactivate user.';
            }
        }
    } elseif (isset($_POST['activate_user']) && $userRole === 'administrator') {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        if ($targetId && $user->activateUser($targetId)) {
            $message = 'User activated.';
        } else {
            $error = 'Failed to activate user.';
        }
    }
}

$profile = $profile ?: $user->getUserById($userId);
$allUsers = $userRole === 'administrator' ? $user->getAllUsers() : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - <?php echo APP_NAME; ?></title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/account-settings.css">
</head>

<body>
<?php include PAGE_PARTIALS . '/header.php'; ?>

<div class="admin-container">
    <main class="admin-content">

        <!-- 🔥 IMPORTANT: settings-page wrapper -->
        <section class="admin-section settings-page">

            <h1>Account Settings</h1>
            <p class="section-desc">Manage your profile, password, and account.</p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- PROFILE -->
            <div class="settings-section">
                <h2>Profile Information</h2>

                <form method="POST" class="settings-form settings-grid">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="text"
                               value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>"
                               disabled class="form-control">
                        <small>Email cannot be changed here.</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone"
                               value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name"
                               value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>">
                    </div>

                    <!-- FULL WIDTH -->
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary full-width">Update Profile</button>
                </form>
            </div>

            <!-- PASSWORD -->
            <div class="settings-section">
                <h2>Password Security</h2>

                <form method="POST" class="settings-form settings-grid settings-grid-3">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary full-width">Change Password</button>
                </form>
            </div>

            <!-- ADMIN USERS -->
            <?php if ($userRole === 'administrator'): ?>
                <div class="settings-section">
                    <h2>User Management</h2>

                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>

                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                            <input type="hidden" name="change_role" value="1">

                                            <select name="role" onchange="this.form.submit()">
                                                <option value="customer" <?php echo $u['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                                <option value="technician" <?php echo $u['role'] === 'technician' ? 'selected' : ''; ?>>Technician</option>
                                                <option value="administrator" <?php echo $u['role'] === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
                                            </select>
                                        </form>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo $u['is_active'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>

                                    <td class="actions">
                                        <?php if ($u['id'] != $userId): ?>
                                            <?php if ($u['is_active']): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                    <input type="hidden" name="deactivate_user" value="1">
                                                    <button class="btn btn-warning"
                                                            onclick="return confirm('Deactivate this user?')">Deactivate</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST">
                                                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                    <input type="hidden" name="activate_user" value="1">
                                                    <button class="btn btn-success">Activate</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- DANGER -->
            <div class="settings-section danger-zone">
                <h2>Danger Zone</h2>

                <p>Deactivating your account will log you out.</p>

                <form method="POST"
                      onsubmit="return confirm('Are you sure you want to deactivate your account?');">
                    <input type="hidden" name="deactivate_self" value="1">
                    <button class="btn btn-danger">Deactivate My Account</button>
                </form>
            </div>

        </section>
    </main>
</div>

<?php include PAGE_PARTIALS . '/footer.php'; ?>
<script type="module" src="frontend/src/pages/account/accountSettings.js"></script>

</body>
</html>