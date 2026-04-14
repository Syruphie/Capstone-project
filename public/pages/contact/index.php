<?php
require_once __DIR__ . '/../bootstrap_paths.php';
require_once PAGE_HANDLERS . '/contact-process-post.php';

$user = new FrontendUser();

if (!$user->isLoggedIn() || $user->getRole() !== 'customer') {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}

$dbUser = $user->getUserById($_SESSION['user_id']);
$result = contact_process_post($dbUser);
$error = $result['error'];
$success = $result['success'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include PAGE_PARTIALS . '/header.php'; ?>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Contact Us</h1>
            <p>If you have questions about your orders, schedules, or our services, please use the form below.</p>
        </div>

        <div class="dashboard-grid full-width contact-layout">
            <div class="dashboard-card feedback-card">
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

            <div class="dashboard-card system-info contact-system-info">
                <h3>Contact Details</h3>
                <p><strong>Laboratory:</strong> <?php echo APP_NAME; ?></p>
                <p><strong>Email:</strong> <?php echo defined('SUPPORT_EMAIL') ? htmlspecialchars(SUPPORT_EMAIL) : 'support@example.com'; ?></p>
                <p><strong>Phone:</strong> +6281802175466</p>
                <p><strong>Address:</strong><br>
                    Jl. Terusan Cisokan Dalam No.5<br>
                    Cihaur Geulis, Kec. Cibeunying Kaler,<br>
                    Kota Bandung, Jawa Barat 40122
                </p>
                <p style="margin-top:10px; color:#6c757d; font-size:13px;">
                    Our team is available during regular business hours to assist with order questions, scheduling, and general inquiries.
                </p>
            </div>
        </div>
    </div>

    <?php include PAGE_PARTIALS . '/footer.php'; ?>
    <script type="module" src="frontend/src/pages/contact/contact.js"></script>
</body>
</html>
