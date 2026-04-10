<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../bootstrap_paths.php';

$error = '';
$user = new FrontendUser();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: ' . app_path('dashboard/index.php'));
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if ($user->login($email, $password)) {
        header('Location: ' . app_path('dashboard/index.php'));
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <a href="<?php echo htmlspecialchars(app_path('index.php'), ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration: none; color: inherit;">
                    <h1><?php echo APP_NAME; ?></h1>
                </a>
                <p>Laboratory Order Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="Enter your email"
                        autocomplete="email"
                        title="Please enter a valid email address, like name@example.com"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        minlength="6"
                        maxlength="32"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="<?php echo htmlspecialchars(app_path('auth/register.php'), ENT_QUOTES, 'UTF-8'); ?>">Register here</a></p>
                <p style="margin-top: 10px;"><a href="<?php echo htmlspecialchars(app_path('index.php'), ENT_QUOTES, 'UTF-8'); ?>">&larr; Back to Home</a></p>
            </div>

            <div class="test-accounts">
                <h3>Test Accounts</h3>
                <div class="account-info">
                    <strong>Administrator:</strong> admin@globentech.com / admin123
                </div>
                <div class="account-info">
                    <strong>Technician:</strong> tech@globentech.com / tech123
                </div>
                <div class="account-info">
                    <strong>Customer:</strong> customer@globentech.com / customer123
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="frontend/src/pages/auth/login.js"></script>
</body>
</html>

