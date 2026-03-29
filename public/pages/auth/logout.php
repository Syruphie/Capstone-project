<?php
require_once __DIR__ . '/../bootstrap_paths.php';

$user = new FrontendUser();
$user->logout();

header('Location: ' . app_path('index.php'));
exit;

