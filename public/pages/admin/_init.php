<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap_paths.php';
require_once PAGE_HANDLERS . '/admin-process-post.php';

$user = new FrontendUser();
$userRole = $user->getRole();

if (!$user->isLoggedIn() || !in_array($userRole, ['administrator', 'technician'], true)) {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}

$userId = (int) $_SESSION['user_id'];

$adminTab = pathinfo((string) ($_SERVER['SCRIPT_NAME'] ?? ''), PATHINFO_FILENAME);

$allowedForTechnician = ['approvals'];
if ($userRole === 'technician' && !in_array($adminTab, $allowedForTechnician, true)) {
    header('Location: ' . app_path('admin/approvals.php'));
    exit;
}

$order = new FrontendOrder();
$equipment = new FrontendEquipment();
$queue = new FrontendQueue();
$sample = new FrontendSample();
$email = new FrontendEmail();

$message = admin_process_post($user, $order, $equipment, $queue, $sample, $email, $userRole, $userId);

$userSearch = '';
$userRoleFilter = '';
$userStatusFilter = '';
$userStatusActive = null;
$usersList = [];

if ($adminTab === 'users') {
    $userSearch = isset($_GET['user_search']) ? trim((string) $_GET['user_search']) : '';
    $userRoleFilter = isset($_GET['user_role']) ? trim((string) $_GET['user_role']) : '';
    $userStatusFilter = isset($_GET['user_status']) ? trim((string) $_GET['user_status']) : '';
    if ($userStatusFilter === 'active') {
        $userStatusActive = true;
    } elseif ($userStatusFilter === 'inactive') {
        $userStatusActive = false;
    }
    $usersList = $user->getAllUsers($userRoleFilter ?: null, $userSearch ?: null, $userStatusActive);
}
