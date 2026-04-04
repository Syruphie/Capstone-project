<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Email.php';

$error = '';
$success = '';
$user = new User();
$emailService = new Email();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: dashboard.php');
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

    // Offensive-word list (server-side mirror of client check)
    $offensiveWords = ['fuck','fucking','shit','bitch','bastard','cunt','dick',
        'pussy','nigger','nigga','faggot','fag','retard','whore','slut',
        'piss','cock','asshole','motherfucker','wanker','twat','prick'];
    $hasOffensive = function(string $text) use ($offensiveWords): bool {
        foreach ($offensiveWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $text)) return true;
        }
        return false;
    };

    // Validation
    if (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (strlen($fullName) > 20) {
        $error = 'Full name must be 20 characters or less';
    } elseif (strlen($email) > 30) {
        $error = 'Email address must be 30 characters or less';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address (must contain @)';
    } elseif (!empty($phone) && !preg_match('/^[0-9]{1,15}$/', $phone)) {
        $error = 'Phone number must contain digits only (max 15)';
    } elseif (!empty($companyName) && strlen($companyName) > 35) {
        $error = 'Company name must be 35 characters or less';
    } elseif (!empty($address) && strlen($address) > 45) {
        $error = 'Address must be 45 characters or less';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (strlen($password) > 35) {
        $error = 'Password must be 35 characters or less';
    } elseif ($hasOffensive($fullName) ||
              (!empty($companyName) && $hasOffensive($companyName)) ||
              (!empty($address) && $hasOffensive($address))) {
        $error = 'Offensive or inappropriate language is not allowed';
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
                header('Location: verify-email.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo ASSET_VERSION; ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-box register-box">
            <div class="logo">
                <a href="index.php" style="text-decoration: none; color: inherit;">
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
                    <br><a href="login.php">Click here to login</a>
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
                        maxlength="20"
                        placeholder="Enter your full name (max 20 chars)"
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
                        maxlength="30"
                        placeholder="Enter your email (must contain @)"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        maxlength="15"
                        pattern="[0-9]{1,15}"
                        inputmode="numeric"
                        placeholder="Digits only, max 15"
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input 
                        type="text" 
                        id="company_name" 
                        name="company_name" 
                        maxlength="35"
                        placeholder="Enter your company name (max 35 chars)"
                        value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea 
                        id="address" 
                        name="address" 
                        rows="3" 
                        maxlength="45"
                        placeholder="Enter your address (max 45 chars)"
                    ><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        maxlength="35"
                        placeholder="Enter password (6–35 characters)"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        maxlength="35"
                        placeholder="Confirm your password"
                    >
                </div>

                <button type="submit" name="register" class="btn btn-primary">Register</button>
            </form>

            <div class="login-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p style="margin-top: 10px;"><a href="index.php">&larr; Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="js/main.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>
