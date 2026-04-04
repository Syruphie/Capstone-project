<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Email.php';

$error = '';
$success = '';
$user = new User();
$email = new Email();

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Check if pending registration exists in session
if (!isset($_SESSION['pending_registration'])) {
    header('Location: register.php');
    exit;
}

$pendingReg = $_SESSION['pending_registration'];

// Check if PIN has expired (10 minutes)
if (time() - $pendingReg['created_at'] > 600) {
    unset($_SESSION['pending_registration']);
    header('Location: register.php?error=expired');
    exit;
}

// Handle PIN verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        $enteredPin = trim($_POST['pin'] ?? '');

        if (empty($enteredPin)) {
            $error = 'Please enter the verification PIN';
        } elseif ($enteredPin !== $pendingReg['pin']) {
            $error = 'Invalid PIN. Please try again.';
        } else {
            // PIN is correct, create the account
            if ($user->register(
                $pendingReg['full_name'],
                $pendingReg['email'],
                $pendingReg['password'],
                $pendingReg['phone'],
                $pendingReg['company_name'],
                $pendingReg['address']
            )) {
                unset($_SESSION['pending_registration']);
                $success = 'Email verified successfully! Your account has been created.';
            } else {
                $error = 'Failed to create account. Email may already exist.';
            }
        }
    } elseif (isset($_POST['resend'])) {
        // Resend PIN
        $newPin = sprintf('%06d', mt_rand(0, 999999));
        $_SESSION['pending_registration']['pin'] = $newPin;
        $_SESSION['pending_registration']['created_at'] = time();

        if ($email->sendVerificationPin($pendingReg['email'], $pendingReg['full_name'], $newPin)) {
            $success = 'A new verification PIN has been sent to your email.';
            $pendingReg = $_SESSION['pending_registration'];
        } else {
            $error = 'Failed to send verification email. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo ASSET_VERSION; ?>">
    <style>
        .pin-input {
            letter-spacing: 10px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
        .email-display {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            color: #667eea;
            font-weight: 500;
        }
        .resend-link {
            text-align: center;
            margin-top: 15px;
        }
        .resend-link button {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 14px;
            text-decoration: underline;
        }
        .resend-link button:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <h1><?php echo APP_NAME; ?></h1>
                <p>Verify Your Email</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success && strpos($success, 'account has been created') !== false): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <br><a href="login.php">Click here to login</a>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success || strpos($success, 'account has been created') === false): ?>
                <div class="email-display">
                    <?php echo htmlspecialchars($pendingReg['email']); ?>
                </div>

                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    We've sent a 6-digit verification PIN to your email address. Please enter it below.
                </p>

                <form method="POST" action="" class="login-form">
                    <div class="form-group">
                        <label for="pin">Verification PIN</label>
                        <input
                            type="text"
                            id="pin"
                            name="pin"
                            required
                            maxlength="6"
                            pattern="[0-9]{6}"
                            placeholder="000000"
                            class="pin-input"
                            autocomplete="off"
                        >
                    </div>

                    <button type="submit" name="verify" class="btn btn-primary">Verify Email</button>
                </form>

                <div class="resend-link">
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="resend">Didn't receive the code? Resend PIN</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="login-footer">
                <p><a href="register.php">Back to Registration</a></p>
            </div>
        </div>
    </div>

    <script src="js/main.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>
