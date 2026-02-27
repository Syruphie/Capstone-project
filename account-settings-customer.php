<?php
require_once 'config/database.php';
require_once 'classes/User.php';

$user = new User();

// Customer only
if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
  header('Location: login.php');
  exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Customer';

// Tab control
$tab = $_GET['tab'] ?? 'profile';

// Alerts
$successMsg = '';
$errorMsg = '';

// Load user details
$currentUser = $user->getUserById($userId);

// ================= PROFILE UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

  $tab = 'profile';

  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $company = trim($_POST['company_name'] ?? '');

  if ($name === '' || $email === '') {
    $errorMsg = 'Name and email are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errorMsg = 'Please enter a valid email address.';
  } else {

    if ($user->updateProfile($userId, $name, $email, $company)) {
      $successMsg = 'Profile updated successfully.';
      $_SESSION['user_name'] = $name;
      $userName = $name;
      $currentUser = $user->getUserById($userId);
    } else {
      $errorMsg = 'Profile update failed. Please try again.';
    }
  }
}

// ================= PASSWORD CHANGE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

  $tab = 'password';

  $currentPassword = $_POST['current_password'] ?? '';
  $newPassword = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    $errorMsg = 'All password fields are required.';
  } elseif ($newPassword !== $confirmPassword) {
    $errorMsg = 'New password and confirm password do not match.';
  } elseif (strlen($newPassword) < 8) {
    $errorMsg = 'New password must be at least 8 characters.';
  } else {

    $result = $user->changePassword($userId, $currentPassword, $newPassword);

    if ($result === true) {
      $successMsg = 'Password updated successfully.';
    } elseif ($result === 'INVALID_CURRENT') {
      $errorMsg = 'Current password is incorrect.';
    } else {
      $errorMsg = 'Password update failed. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Settings -
    <?php echo APP_NAME; ?>
  </title>

  <link rel="stylesheet" href="css/style.css">

  <style>
    :root {
      --text: #0f172a;
      --muted: #64748b;
      --border: rgba(15, 23, 42, .10);
    }

    /* PAGE WRAPPER */
    .dashboard-container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 40px 20px 60px;
    }

    /* SHARP OUTER HEADER */
    .welcome-section {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 32px;
      margin-bottom: 28px;
      box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }

    .welcome-section h1 {
      font-size: 42px;
      font-weight: 900;
      margin: 0 0 8px;
      letter-spacing: -1px;
      color: var(--text);
    }

    .welcome-section p {
      margin: 0;
      color: var(--muted);
      font-size: 16px;
    }

    .role-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 900;
      background: rgba(91, 74, 230, .12);
      color: #4338ca;
      border: 1px solid rgba(91, 74, 230, .18);
      height: fit-content;
    }

    /* ALERTS */
    .alert {
      border-radius: 12px;
      padding: 14px 18px;
      margin-bottom: 18px;
      border: 1px solid var(--border);
      background: #fff;
      box-shadow: 0 10px 30px rgba(15, 23, 42, .10);
    }

    /* SHARP OUTER MAIN CARD */
    .dashboard-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 0;
      box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
      overflow: hidden;
    }

    .card-head {
      padding: 18px 22px;
      border-bottom: 1px solid var(--border);
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .tab-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 14px;
      border-radius: 16px;
      font-weight: 900;
      font-size: 13px;
      text-decoration: none !important;
      border: 1px solid rgba(15, 23, 42, .12);
      color: var(--text);
      background: #fff;
    }

    .tab-link.is-active {
      color: #fff;
      border: 0;
      background: linear-gradient(90deg, #5b4ae6, #7c3aed);
      box-shadow: 0 12px 24px rgba(91, 74, 230, .20);
    }

    .card-body {
      padding: 24px 22px 26px;
    }

    h2 {
      margin: 0 0 8px;
      font-size: 24px;
      font-weight: 900;
      color: var(--text);
    }

    .section-desc {
      margin: 0 0 18px;
      color: var(--muted);
      line-height: 1.6;
    }

    /* FORM */
    .form-row {
      margin-bottom: 14px;
    }

    label {
      font-weight: 800;
      font-size: 14px;
      color: var(--text);
      display: block;
      margin-bottom: 6px;
    }

    input {
      width: 100%;
      padding: 14px 16px;
      border-radius: 14px;
      /* rounded inside */
      border: 1px solid rgba(15, 23, 42, .14);
      font-size: 14px;
      outline: none;
    }

    input:focus {
      border-color: #5b4ae6;
      box-shadow: 0 0 0 4px rgba(91, 74, 230, .14);
    }

    /* BUTTON */
    .btn-primary-solid {
      display: inline-block;
      padding: 14px 18px;
      border-radius: 16px;
      font-weight: 900;
      font-size: 14px;
      color: #fff;
      border: 0;
      cursor: pointer;
      background: linear-gradient(90deg, #5b4ae6, #7c3aed);
      box-shadow: 0 12px 24px rgba(91, 74, 230, .25);
      text-decoration: none !important;
    }

    .btn-primary-solid:hover {
      filter: brightness(1.05);
    }

    .actions {
      margin-top: 14px;
      display: flex;
      justify-content: flex-end;
    }

    @media(max-width:900px) {
      .dashboard-container {
        padding: 30px 16px;
      }

      .actions {
        justify-content: stretch;
      }

      .btn-primary-solid {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <div class="dashboard-container">

    <div class="welcome-section">
      <div>
        <h1>Account Settings</h1>
        <p>Manage your profile and security preferences.</p>
      </div>
      <div class="role-badge">
        Customer •
        <?php echo htmlspecialchars($userName); ?>
      </div>
    </div>

    <?php if ($successMsg): ?>
      <div class="alert alert-success">
        <strong>Success:</strong>
        <?php echo htmlspecialchars($successMsg); ?>
      </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
      <div class="alert alert-danger">
        <strong>Error:</strong>
        <?php echo htmlspecialchars($errorMsg); ?>
      </div>
    <?php endif; ?>

    <div class="dashboard-card">

      <div class="card-head">
        <a href="?tab=profile" class="tab-link <?php echo ($tab === 'profile') ? 'is-active' : ''; ?>">
          Profile
        </a>
        <a href="?tab=password" class="tab-link <?php echo ($tab === 'password') ? 'is-active' : ''; ?>">
          Change Password
        </a>
      </div>

      <div class="card-body">

        <?php if ($tab === 'profile'): ?>

          <h2>Profile</h2>
          <p class="section-desc">Update your personal and company information.</p>

          <form method="POST">
            <div class="form-row">
              <label>Full Name</label>
              <input type="text" name="name"
                value="<?php echo htmlspecialchars($currentUser['full_name'] ?? $userName); ?>" required>
            </div>

            <div class="form-row">
              <label>Email</label>
              <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>"
                required>
            </div>

            <div class="form-row">
              <label>Company Name</label>
              <input type="text" name="company_name"
                value="<?php echo htmlspecialchars($currentUser['company_name'] ?? ''); ?>">
            </div>

            <div class="actions">
              <button type="submit" name="update_profile" class="btn-primary-solid">
                Save Changes
              </button>
            </div>
          </form>

        <?php else: ?>

          <h2>Change Password</h2>
          <p class="section-desc">Enter your current password and choose a new one.</p>

          <form method="POST">
            <div class="form-row">
              <label>Current Password</label>
              <input type="password" name="current_password" required>
            </div>

            <div class="form-row">
              <label>New Password</label>
              <input type="password" name="new_password" minlength="8" required>
            </div>

            <div class="form-row">
              <label>Confirm Password</label>
              <input type="password" name="confirm_password" minlength="8" required>
            </div>

            <div class="actions">
              <button type="submit" name="change_password" class="btn-primary-solid">
                Update Password
              </button>
            </div>
          </form>

        <?php endif; ?>

      </div>
    </div>

  </div>

  <?php include 'includes/footer.php'; ?>
  <script src="js/main.js"></script>
</body>

</html>