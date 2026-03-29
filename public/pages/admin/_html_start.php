<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($__adminTitle) ? htmlspecialchars($__adminTitle, ENT_QUOTES, 'UTF-8') : 'Admin Panel'; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include PAGE_PARTIALS . '/header.php'; ?>

    <div class="admin-container">
        <main class="admin-content">
