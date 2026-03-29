<?php
require_once __DIR__ . '/../bootstrap_paths.php';

$error = '';
$success = '';
$user = new FrontendUser();
$emailService = new FrontendEmail();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: ' . app_path('dashboard/index.php'));
    exit;
}

// Check for expired PIN error from verify-email.php
if (isset($_GET['error']) && $_GET['error'] === 'expired') {
    $error = 'Verification PIN has expired. Please register again.';
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $companyName = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    // Validation
    if (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email already exists
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already exists';
        } else {
            // Generate 6-digit PIN
            $pin = sprintf('%06d', mt_rand(0, 999999));

            // Store registration data in session
            $_SESSION['pending_registration'] = [
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'phone' => $phone,
                'company_name' => $companyName,
                'address' => $address,
                'pin' => $pin,
                'created_at' => time()
            ];

            // Send verification email
            if ($emailService->sendVerificationPin($email, $fullName, $pin)) {
                header('Location: ' . app_path('auth/verify-email.php'));
                exit;
            } else {
                $error = 'Failed to send verification email. Please try again.';
                unset($_SESSION['pending_registration']);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box register-box">
            <div class="logo">
                <a href="<?php echo htmlspecialchars(app_path('index.php'), ENT_QUOTES, 'UTF-8'); ?>" class="logo-link">
                    <h1><?php echo APP_NAME; ?></h1>
                </a>
                <p>Create Your Account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <br><a href="<?php echo htmlspecialchars(app_path('auth/login.php'), ENT_QUOTES, 'UTF-8'); ?>">Click here to login</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        required 
                        placeholder="Enter your full name"
                        value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="Enter your email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        placeholder="Enter your phone number"
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input 
                        type="text" 
                        id="company_name" 
                        name="company_name" 
                        placeholder="Enter your company name"
                        value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="3" 
                        placeholder="Enter your address"
                    ><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Enter password (min. 6 characters)"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        placeholder="Confirm your password"
                    >
                </div>

                <button type="submit" name="register" class="btn btn-primary">Register</button>
            </form>

            <div class="login-footer">
                <p>Already have an account? <a href="<?php echo htmlspecialchars(app_path('auth/login.php'), ENT_QUOTES, 'UTF-8'); ?>">Login here</a></p>
                <p style="margin-top: 10px;"><a href="<?php echo htmlspecialchars(app_path('index.php'), ENT_QUOTES, 'UTF-8'); ?>">&larr; Back to Home</a></p>
            </div>
        </div>
    </div>

    <script type="module" src="frontend/src/pages/auth/register.js"></script>
</body>
</html>

