<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

require_once __DIR__ . '/FrontendUser.php';
require_once __DIR__ . '/FrontendOrder.php';
require_once __DIR__ . '/FrontendSample.php';
require_once __DIR__ . '/FrontendQueue.php';
require_once __DIR__ . '/FrontendEquipment.php';
require_once __DIR__ . '/FrontendOrderType.php';
require_once __DIR__ . '/FrontendEmail.php';
require_once __DIR__ . '/FrontendPayment.php';

