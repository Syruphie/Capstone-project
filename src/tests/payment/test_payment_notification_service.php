<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('PaymentNotificationService');

$db = getTestDb();
$paymentRepo = makePaymentRepository();
$notificationRepo = makeNotificationRepository();
$service = new PaymentNotificationService($notificationRepo);

$paymentRepo->upsertFromProviderData([
    'id' => 'pi_notify_success_001',
    'amount' => 5000,
    'currency' => 'cad',
    'status' => 'succeeded',
    'payment_method_types' => ['card'],
], 1, 1);

$paymentId = $paymentRepo->getPaymentIdByIntent('pi_notify_success_001');
assertNotNull($paymentId, 'payment should exist before creating notifications');

$service->createNotificationsForPaymentState((int)$paymentId, 1, 1, 'succeeded', 50.0, null);

$rows = $db->query('SELECT user_id, notification_type, severity FROM notifications ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
assertCountSame(1, $rows, 'succeeded low-value payment should create one customer notification');
assertSame(1, (int)$rows[0]['user_id'], 'customer notification should target paying customer');
assertSame('payment_confirmation', $rows[0]['notification_type'], 'customer notification type should be payment_confirmation');
assertSame('info', $rows[0]['severity'], 'customer confirmation severity should be info');
printPass('creates customer confirmation for succeeded payment');

require __DIR__ . '/../reset_test_db.php';

$db = getTestDb();
$paymentRepo = makePaymentRepository();
$notificationRepo = makeNotificationRepository();
$service = new PaymentNotificationService($notificationRepo);

$paymentRepo->upsertFromProviderData([
    'id' => 'pi_notify_failure_001',
    'amount' => 20000,
    'currency' => 'cad',
    'status' => 'failed',
    'payment_method_types' => ['card'],
    'last_payment_error' => ['message' => 'Do not honor - possible fraud flagged by issuer'],
], 1, 1);

$failedPaymentId = $paymentRepo->getPaymentIdByIntent('pi_notify_failure_001');
assertNotNull($failedPaymentId, 'failed payment should exist before creating notifications');

$service->createNotificationsForPaymentState(
    (int)$failedPaymentId,
    1,
    1,
    'failed',
    200.0,
    'Do not honor - possible fraud flagged by issuer'
);

$rows = $db->query('SELECT user_id, notification_type, severity FROM notifications ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
assertCountSame(2, $rows, 'failed suspicious payment should create customer and admin notifications');

assertSame(1, (int)$rows[0]['user_id'], 'first notification should target customer');
assertSame('payment_failure', $rows[0]['notification_type'], 'customer notification should indicate payment failure');
assertSame('warning', $rows[0]['severity'], 'payment failure severity should be warning');

assertSame(3, (int)$rows[1]['user_id'], 'second notification should target active admin');
assertSame('suspicious_payment', $rows[1]['notification_type'], 'admin notification should be suspicious payment alert');
assertSame('critical', $rows[1]['severity'], 'suspicious payment severity should be critical');
printPass('creates customer and admin notifications for suspicious failures');

$service->createNotificationsForPaymentState(null, 1, 1, 'succeeded', 1200.0, null);
assertCountSame(2, $db->query('SELECT * FROM notifications')->fetchAll(PDO::FETCH_ASSOC), 'null payment id should skip notification creation');
printPass('skips notifications when payment id is null');

