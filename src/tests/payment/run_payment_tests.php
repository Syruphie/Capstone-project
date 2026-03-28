<?php
declare(strict_types=1);

$tests = [
    __DIR__ . '/test_payment_provider.php',
    __DIR__ . '/test_payment_repository.php',
    __DIR__ . '/test_payment_status_service.php',
    __DIR__ . '/test_payment_service_refresh_sync.php',
    __DIR__ . '/test_payment_notification_service.php',
    __DIR__ . '/test_invoice_service.php',
];

foreach ($tests as $testFile) {
    require $testFile;
}

echo "\nPayment domain tests passed.\n";

