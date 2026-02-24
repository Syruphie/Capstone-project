<?php
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Email.php';

$user = new User();

// Only logged-in customers can access
if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: login.php');
    exit;
}

$dbUser = $user->getUserById($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $messageBody = trim($_POST['message'] ?? '');
    $orderNumber = trim($_POST['order_number'] ?? '');

    if ($subject === '' || $messageBody === '') {
        $error = 'Please fill in both subject and message.';
    } else {
        $emailService = new Email();
        $to = defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@example.com';

        $customerName = $dbUser['full_name'] ?? '';
        $customerEmail = $dbUser['email'] ?? '';

        $body = "<p>You have received a new contact message from the customer portal.</p>";
        $body .= "<p><strong>Name:</strong> " . htmlspecialchars($customerName) . "<br>";
        $body .= "<strong>Email:</strong> " . htmlspecialchars($customerEmail) . "</p>";
        if ($orderNumber !== '') {
            $body .= "<p><strong>Related Order #:</strong> " . htmlspecialchars($orderNumber) . "</p>";
        }
        $body .= "<p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>";
        $body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($messageBody)) . "</p>";

        if ($emailService->send($to, '[Contact] ' . $subject, $body, true)) {
            $success = 'Your message has been sent. Our team will get back to you.';
        } else {
            $error = 'Failed to send your message. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Contact Us</h1>
            <p>If you have questions about your orders, schedules, or our services, please use the form below.</p>
        </div>

        <div class="dashboard-grid full-width">
            <div class="dashboard-card">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" class="form-control" value="<?php echo htmlspecialchars($dbUser['full_name'] ?? ''); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="email">Your Email</label>
                        <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($dbUser['email'] ?? ''); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="order_number">Related Order # (optional)</label>
                        <input type="text" id="order_number" name="order_number" class="form-control" value="<?php echo htmlspecialchars($_POST['order_number'] ?? ''); ?>" placeholder="e.g. ORD-20250127-0001">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" class="form-control" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>

            <div class="dashboard-card system-info">
                <h3>Contact Details</h3>
                <p><strong>Laboratory:</strong> <?php echo APP_NAME; ?></p>
                <p><strong>Email:</strong> <?php echo defined('SUPPORT_EMAIL') ? htmlspecialchars(SUPPORT_EMAIL) : 'support@example.com'; ?></p>
                <p><strong>Phone:</strong> +1 (555) 123-4567</p>
                <p><strong>Address:</strong><br>
                    123 Research Park Way<br>
                    Calgary, AB<br>
                    Canada
                </p>
                <p style="margin-top:10px; color:#6c757d; font-size:13px;">
                    Our team is available during regular business hours to assist with order questions, scheduling, and general inquiries.
                </p>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>

