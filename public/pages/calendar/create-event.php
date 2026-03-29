<?php
require_once __DIR__ . '/../bootstrap_paths.php';

$user = new FrontendUser();
if (!$user->isLoggedIn()) {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/events.css">
</head>
<body>
    <?php include PAGE_PARTIALS . '/header.php'; ?>

    <div class="container">
        <h2>Create Event</h2>

        <form id="createEventForm" class="event-form">
            <div class="form-group">
                <label>Event Title</label>
                <input type="text" placeholder="Enter event title" required>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" required>
            </div>

            <div class="form-group">
                <label>Start Time</label>
                <input type="time" required>
            </div>

            <div class="form-group">
                <label>End Time</label>
                <input type="time" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea placeholder="Event details..."></textarea>
            </div>

            <button type="submit" class="btn-primary">Create Event</button>
        </form>
    </div>

    <?php include PAGE_PARTIALS . '/footer.php'; ?>
</body>
</html>
