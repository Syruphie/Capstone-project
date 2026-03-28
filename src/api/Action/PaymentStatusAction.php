<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class PaymentStatusAction
{
    public function handle(): void
    {
        ApiAuth::requireUser();

        $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
        $refresh = isset($_GET['refresh']) ? (bool)intval($_GET['refresh']) : true;
        $customerId = (int)($_SESSION['user_id'] ?? 0);

        if ($orderId <= 0 || $customerId <= 0) {
            JsonResponse::send(['success' => false, 'error' => 'Missing order_id'], 400);
            return;
        }

        try {
            $payment = new FrontendPayment();
            $status = $payment->getPaymentStatusForOrder($orderId, $customerId, $refresh);

            if (!$status) {
                JsonResponse::send([
                    'success' => true,
                    'found' => false,
                    'message' => 'No payment found for this order yet',
                ]);
                return;
            }

            JsonResponse::send([
                'success' => true,
                'found' => true,
                'payment' => [
                    'id' => (int)$status['id'],
                    'order_id' => (int)$status['order_id'],
                    'amount' => (float)$status['amount'],
                    'currency' => $status['currency'],
                    'status' => $status['status'],
                    'failure_reason' => $status['failure_reason'],
                    'paid_at' => $status['paid_at'],
                    'updated_at' => $status['updated_at'],
                ],
            ]);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

