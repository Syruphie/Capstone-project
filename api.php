<?php
/**
 * Centralized API router.
 * Usage: /api.php?endpoint=calendar-data
 *
 * Stripe webhooks (POST): /api.php?endpoint=payment-webhook
 * Set STRIPE_WEBHOOK_SECRET in Application Settings to the signing secret for that endpoint
 * (Dashboard: Developers → Webhooks → your endpoint → Signing secret).
 */

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/classes/Frontend/bootstrap.php';

require_once __DIR__ . '/src/api/Action/CalendarDataAction.php';
require_once __DIR__ . '/src/api/Action/CalendarReorderAction.php';
require_once __DIR__ . '/src/api/Action/CalendarRescheduleAction.php';
require_once __DIR__ . '/src/api/Action/EquipmentAddAction.php';
require_once __DIR__ . '/src/api/Action/OrderTypesAction.php';
require_once __DIR__ . '/src/api/Action/OrderCompleteAction.php';
require_once __DIR__ . '/src/api/Action/InvoiceAction.php';
require_once __DIR__ . '/src/api/Action/PaymentStatusAction.php';
require_once __DIR__ . '/src/api/Action/PaymentWebhookAction.php';
require_once __DIR__ . '/src/api/Action/ReportsAction.php';
require_once __DIR__ . '/src/api/Action/CreatePaymentIntentAction.php';

$routes = [
    'calendar-data' => 'CalendarDataAction',
    'calendar-reorder' => 'CalendarReorderAction',
    'calendar-reschedule' => 'CalendarRescheduleAction',
    'equipment-add' => 'EquipmentAddAction',
    'order-types' => 'OrderTypesAction',
    'order-complete' => 'OrderCompleteAction',
    'invoice' => 'InvoiceAction',
    'payment-status' => 'PaymentStatusAction',
    'payment-webhook' => 'PaymentWebhookAction',
    'reports' => 'ReportsAction',
    'create-payment-intent' => 'CreatePaymentIntentAction',
];

$endpoint = isset($_GET['endpoint']) ? trim((string) $_GET['endpoint']) : '';

if ($endpoint === '' || !isset($routes[$endpoint])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
    exit;
}

$actionClass = $routes[$endpoint];
(new $actionClass())->handle();

