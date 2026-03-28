<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/JsonResponse.php';

class PaymentWebhookAction
{
    public function handle(): void
    {
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;

        if (!$payload) {
            JsonResponse::send(['success' => false, 'error' => 'Missing payload'], 400);
            return;
        }

        try {
            $payment = new FrontendPayment();
            $result = $payment->handleWebhook($payload, $signature);

            if (!($result['ok'] ?? false)) {
                JsonResponse::send(['success' => false, 'error' => $result['error'] ?? 'Webhook rejected'], 400);
                return;
            }

            JsonResponse::send(['success' => true, 'message' => $result['message'] ?? 'Processed']);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

