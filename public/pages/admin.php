<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap_paths.php';

/**
 * Legacy entry: ?tab= was used when admin lived in one file.
 * Redirects to the matching routable page under admin/.
 */
$tab = isset($_GET['tab']) ? preg_replace('/[^a-z]/', '', (string) $_GET['tab']) : 'approvals';
$map = [
    'approvals' => 'admin/approvals.php',
    'equipment' => 'admin/equipment.php',
    'samples' => 'admin/samples.php',
    'users' => 'admin/users.php',
    'reports' => 'admin/reports.php',
    'catalogue' => 'admin/catalogue.php',
];
$target = $map[$tab] ?? 'admin/approvals.php';
$query = $_GET;
unset($query['tab']);
$qs = http_build_query($query);
$path = app_path($target) . ($qs !== '' ? '?' . $qs : '');
header('Location: ' . $path, true, 301);
exit;
