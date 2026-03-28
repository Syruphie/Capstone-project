<?php
declare(strict_types=1);

require_once __DIR__ . '/../Support/ApiAuth.php';
require_once __DIR__ . '/../Support/JsonResponse.php';

class InvoiceAction
{
    public function handle(): void
    {
        ApiAuth::requireUser();

        $orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
        $customerId = (int) $_SESSION['user_id'];

        if ($orderId <= 0) {
            JsonResponse::send(['success' => false, 'error' => 'Missing order_id'], 400);
            return;
        }

        try {
            $payment = new FrontendPayment();
            $invoice = $payment->getInvoiceByOrderAndCustomer($orderId, $customerId);

            if (!$invoice) {
                $payment->getPaymentStatusForOrder($orderId, $customerId, true);
                $invoice = $payment->getInvoiceByOrderAndCustomer($orderId, $customerId);
            }

            if (!$invoice) {
                JsonResponse::send(['success' => true, 'found' => false]);
                return;
            }

            JsonResponse::send([
                'success' => true,
                'found' => true,
                'invoice' => [
                    'invoice_number' => $invoice['invoice_number'],
                    'payment_status' => $invoice['payment_status'],
                    'amount' => (float) $invoice['amount'],
                    'currency' => $invoice['currency'],
                    'created_at' => $invoice['created_at']
                ]
            ]);
        } catch (Exception $e) {
            JsonResponse::send(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

