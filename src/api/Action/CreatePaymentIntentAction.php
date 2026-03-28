<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class CreatePaymentIntentAction
{
    public function handle(): void
    {
        ApiAuth::requireUser();

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $orderId = isset($input['order_id']) ? (int)$input['order_id'] : 0;
        $customerId = (int)($_SESSION['user_id'] ?? 0);

        if ($email === '' || $orderId <= 0 || $customerId <= 0) {
            JsonResponse::send(['success' => false, 'error' => 'Missing required fields'], 400);
            return;
        }

        $order = new FrontendOrder();
        $orderData = $order->getOrderById($orderId);

        if (!$orderData || (int)$orderData['customer_id'] !== $customerId) {
            JsonResponse::send(['success' => false, 'error' => 'Invalid order'], 403);
            return;
        }

        $amount = (float)($orderData['total_cost'] ?? 0);
        if ($amount <= 0) {
            $amount = 20.00;
        }

        try {
            $payment = new FrontendPayment();
            $paymentIntent = $payment->createPaymentIntentForOrder($orderId, $customerId, $email, $amount, 'cad');

            JsonResponse::send([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getError()->message], 400);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

