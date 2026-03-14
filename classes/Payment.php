<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Email.php';
require_once __DIR__ . '/../checkout/stripe_env_loader.php';
require_once __DIR__ . '/../vendor/autoload.php';

class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? '');
    }

    public function createPaymentIntentForOrder($orderId, $customerId, $email, $amount, $currency = 'cad') {
        $amountCents = (int) round($amount * 100);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never'
            ],
            'receipt_email' => $email,
            'metadata' => [
                'order_id' => (string) $orderId,
                'customer_id' => (string) $customerId
            ]
        ]);

        $this->upsertPaymentFromIntent($paymentIntent, $orderId, $customerId);
        $this->syncOrderStatusByPayment($orderId, $paymentIntent->status);

        return $paymentIntent;
    }

    public function getPaymentStatusForOrder($orderId, $customerId, $refreshFromProvider = true) {
        $stmt = $this->db->prepare(
            "SELECT * FROM payments WHERE order_id = ? AND customer_id = ? ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$orderId, $customerId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            return null;
        }

        if ($refreshFromProvider && !empty($payment['provider_payment_intent_id'])) {
            $intent = \Stripe\PaymentIntent::retrieve($payment['provider_payment_intent_id']);
            $this->upsertPaymentFromIntent($intent, $orderId, $customerId);

            $stmt->execute([$orderId, $customerId]);
            $payment = $stmt->fetch();
        }

        return $payment;
    }

    public function upsertPaymentFromIntent($intent, $orderId = null, $customerId = null) {
        $intentArr = is_array($intent) ? $intent : $intent->toArray();

        if ($orderId === null) {
            $orderId = (int) ($intentArr['metadata']['order_id'] ?? 0);
        }
        if ($customerId === null) {
            $customerId = (int) ($intentArr['metadata']['customer_id'] ?? 0);
        }

        if ($orderId <= 0 || $customerId <= 0) {
            return false;
        }

        $providerPayload = json_encode($intentArr);
        $paymentMethodType = null;
        if (!empty($intentArr['payment_method_types']) && is_array($intentArr['payment_method_types'])) {
            $paymentMethodType = $intentArr['payment_method_types'][0] ?? null;
        }

        $status = $intentArr['status'] ?? 'created';
        $failureReason = $intentArr['last_payment_error']['message'] ?? null;
        $amount = ((int) ($intentArr['amount'] ?? 0)) / 100;
        $currency = strtoupper($intentArr['currency'] ?? 'cad');
        $paidAt = null;

        if (isset($intentArr['status']) && $intentArr['status'] === 'succeeded') {
            $paidAt = date('Y-m-d H:i:s');
        }

        $sql = "INSERT INTO payments
                (order_id, customer_id, provider, provider_payment_intent_id, amount, currency, status, payment_method_type, failure_reason, paid_at, provider_payload)
                VALUES (?, ?, 'stripe', ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    amount = VALUES(amount),
                    currency = VALUES(currency),
                    status = VALUES(status),
                    payment_method_type = VALUES(payment_method_type),
                    failure_reason = VALUES(failure_reason),
                    paid_at = VALUES(paid_at),
                    provider_payload = VALUES(provider_payload),
                    updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            $orderId,
            $customerId,
            $intentArr['id'],
            $amount,
            $currency,
            $status,
            $paymentMethodType,
            $failureReason,
            $paidAt,
            $providerPayload
        ]);

        if ($ok) {
            $paymentId = $this->getPaymentIdByIntent($intentArr['id']);
            $this->syncOrderStatusByPayment($orderId, $status);
            $this->createNotificationsForPaymentState($paymentId, $orderId, $customerId, $status, $amount, $failureReason);

            if ($status === 'succeeded') {
                $this->createAccountingSyncRecord($paymentId, $orderId);
                $this->generateInvoiceForPayment($paymentId);
                $this->emailReceiptForPayment($paymentId);
            }
        }

        return $ok;
    }

    public function handleWebhook($payload, $signatureHeader = null) {
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        try {
            if (!empty($webhookSecret) && $signatureHeader) {
                $event = \Stripe\Webhook::constructEvent($payload, $signatureHeader, $webhookSecret);
            } else {
                $event = json_decode($payload, true);
            }
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $eventArr = is_array($event)
            ? $event
            : (method_exists($event, 'toArray') ? $event->toArray() : json_decode(json_encode($event), true));

        $eventId = $eventArr['id'] ?? null;
        $eventType = $eventArr['type'] ?? '';
        $intent = $eventArr['data']['object'] ?? null;

        if (!$eventId || !$intent) {
            return ['ok' => false, 'error' => 'Invalid event payload'];
        }

        if ($this->paymentEventExists($eventId)) {
            return ['ok' => true, 'message' => 'Event already processed'];
        }

        $intentId = $intent['id'] ?? '';
        $orderId = (int) ($intent['metadata']['order_id'] ?? 0);
        $customerId = (int) ($intent['metadata']['customer_id'] ?? 0);

        // Fallback for events without metadata: resolve known payment record by intent id.
        if (($orderId <= 0 || $customerId <= 0) && !empty($intentId)) {
            $existingPayment = $this->getPaymentByIntent($intentId);
            if ($existingPayment) {
                $orderId = (int) $existingPayment['order_id'];
                $customerId = (int) $existingPayment['customer_id'];
            }
        }

        if ($orderId > 0 && $customerId > 0) {
            $this->upsertPaymentFromIntent($intent, $orderId, $customerId);
        }

        $paymentId = $this->getPaymentIdByIntent($intentId);
        $stmt = $this->db->prepare(
            "INSERT INTO payment_events (payment_id, provider, provider_event_id, event_type, payload, processed_at)
             VALUES (?, 'stripe', ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $paymentId ?: null,
            $eventId,
            $eventType,
            json_encode($eventArr)
        ]);

        return ['ok' => true, 'message' => 'Processed'];
    }

    public function generateInvoiceForPayment($paymentId) {
        $stmt = $this->db->prepare(
            "SELECT i.id FROM invoices i WHERE i.payment_id = ? LIMIT 1"
        );
        $stmt->execute([$paymentId]);
        if ($stmt->fetch()) {
            return true;
        }

        $payment = $this->getPaymentById($paymentId);
        if (!$payment || $payment['status'] !== 'succeeded') {
            return false;
        }

        $orderStmt = $this->db->prepare(
            "SELECT o.order_number, o.total_cost, u.full_name, u.email, u.company_name
             FROM orders o
             JOIN users u ON u.id = o.customer_id
             WHERE o.id = ?"
        );
        $orderStmt->execute([$payment['order_id']]);
        $orderData = $orderStmt->fetch();

        if (!$orderData) {
            return false;
        }

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad((string) $paymentId, 6, '0', STR_PAD_LEFT);
        $transactionDetails = [
            'payment_intent_id' => $payment['provider_payment_intent_id'],
            'amount' => $payment['amount'],
            'currency' => $payment['currency'],
            'status' => $payment['status'],
            'paid_at' => $payment['paid_at'],
            'order_number' => $orderData['order_number'],
            'customer_email' => $orderData['email']
        ];

        $receiptHtml = $this->buildReceiptHtml($invoiceNumber, $payment, $orderData);

        $insert = $this->db->prepare(
            "INSERT INTO invoices (payment_id, order_id, customer_id, invoice_number, transaction_details, receipt_html)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        return $insert->execute([
            $paymentId,
            $payment['order_id'],
            $payment['customer_id'],
            $invoiceNumber,
            json_encode($transactionDetails),
            $receiptHtml
        ]);
    }

    public function getInvoiceByOrderAndCustomer($orderId, $customerId) {
        $stmt = $this->db->prepare(
            "SELECT i.*, p.status as payment_status, p.amount, p.currency
             FROM invoices i
             JOIN payments p ON p.id = i.payment_id
             WHERE i.order_id = ? AND i.customer_id = ?
             ORDER BY i.created_at DESC
             LIMIT 1"
        );
        $stmt->execute([$orderId, $customerId]);
        return $stmt->fetch();
    }

    private function getPaymentIdByIntent($intentId) {
        if (!$intentId) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id FROM payments WHERE provider_payment_intent_id = ? LIMIT 1");
        $stmt->execute([$intentId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id'] : null;
    }

    private function getPaymentByIntent($intentId) {
        if (!$intentId) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM payments WHERE provider_payment_intent_id = ? LIMIT 1");
        $stmt->execute([$intentId]);
        return $stmt->fetch();
    }

    private function getPaymentById($paymentId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ? LIMIT 1");
        $stmt->execute([$paymentId]);
        return $stmt->fetch();
    }

    private function paymentEventExists($providerEventId) {
        $stmt = $this->db->prepare("SELECT id FROM payment_events WHERE provider_event_id = ? LIMIT 1");
        $stmt->execute([$providerEventId]);
        return (bool) $stmt->fetch();
    }

    private function syncOrderStatusByPayment($orderId, $paymentStatus) {
        $targetStatus = null;

        if (in_array($paymentStatus, ['processing', 'requires_action', 'requires_payment_method'])) {
            $targetStatus = 'payment_pending';
        } elseif ($paymentStatus === 'succeeded') {
            $targetStatus = 'payment_confirmed';
        } elseif (in_array($paymentStatus, ['canceled', 'failed'])) {
            $targetStatus = 'payment_pending';
        }

        if ($targetStatus) {
            if ($targetStatus === 'payment_pending') {
                $currentStmt = $this->db->prepare("SELECT status FROM orders WHERE id = ? LIMIT 1");
                $currentStmt->execute([$orderId]);
                $current = $currentStmt->fetchColumn();

                // Prevent out-of-order webhooks from downgrading confirmed/completed orders.
                if (in_array($current, ['payment_confirmed', 'in_queue', 'preparation_in_progress', 'testing_in_progress', 'results_available', 'completed'])) {
                    return;
                }
            }

            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$targetStatus, $orderId]);
        }
    }

    private function createNotificationsForPaymentState($paymentId, $orderId, $customerId, $status, $amount, $failureReason) {
        if (!$paymentId) {
            return;
        }

        if ($status === 'succeeded') {
            $this->insertNotification(
                $customerId,
                $orderId,
                $paymentId,
                'payment_confirmation',
                'info',
                'Payment confirmed',
                'Your payment was confirmed and your order is now progressing.'
            );

            if ((float) $amount >= 1000) {
                $this->notifyAdmins(
                    $orderId,
                    $paymentId,
                    'high_value_payment',
                    'warning',
                    'High-value payment received',
                    'A high-value payment was received and should be reviewed by an administrator.'
                );
            }
        }

        if (in_array($status, ['requires_payment_method', 'failed', 'canceled'])) {
            $this->insertNotification(
                $customerId,
                $orderId,
                $paymentId,
                'payment_failure',
                'warning',
                'Payment requires attention',
                'Payment failed or needs a retry. Please update your payment method and try again.'
            );

            $message = strtolower((string) $failureReason);
            if (strpos($message, 'fraud') !== false || strpos($message, 'do not honor') !== false) {
                $this->notifyAdmins(
                    $orderId,
                    $paymentId,
                    'suspicious_payment',
                    'critical',
                    'Suspicious payment detected',
                    'A payment failure indicates potential suspicious activity and should be investigated.'
                );
            }
        }
    }

    private function insertNotification($userId, $orderId, $paymentId, $type, $severity, $title, $message) {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, order_id, payment_id, notification_type, severity, title, message)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $orderId, $paymentId, $type, $severity, $title, $message]);
    }

    private function notifyAdmins($orderId, $paymentId, $type, $severity, $title, $message) {
        $stmt = $this->db->query("SELECT id FROM users WHERE role = 'administrator' AND is_active = 1");
        $admins = $stmt->fetchAll();
        foreach ($admins as $admin) {
            $this->insertNotification((int) $admin['id'], $orderId, $paymentId, $type, $severity, $title, $message);
        }
    }

    private function createAccountingSyncRecord($paymentId, $orderId) {
        $period = date('Y-m');
        $stmt = $this->db->prepare(
            "INSERT INTO accounting_sync (payment_id, order_id, sync_status, reporting_period, synced_at)
             VALUES (?, ?, 'synced', ?, NOW())"
        );
        $stmt->execute([$paymentId, $orderId, $period]);
    }

    private function emailReceiptForPayment($paymentId) {
        $payment = $this->getPaymentById($paymentId);
        if (!$payment) {
            return false;
        }

        $invoice = $this->getInvoiceByOrderAndCustomer($payment['order_id'], $payment['customer_id']);
        if (!$invoice) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$payment['customer_id']]);
        $customer = $stmt->fetch();
        if (!$customer) {
            return false;
        }

        $subject = 'Payment Receipt - ' . APP_NAME . ' - ' . $invoice['invoice_number'];
        $body = $invoice['receipt_html'] . '<p style="font-family:Arial,sans-serif;">You can also view this invoice in your account order history.</p>';

        $email = new Email();
        return $email->send($customer['email'], $subject, $body, true);
    }

    private function buildReceiptHtml($invoiceNumber, $payment, $orderData) {
        $amount = number_format((float) $payment['amount'], 2);
        $paidAt = !empty($payment['paid_at']) ? date('Y-m-d H:i', strtotime($payment['paid_at'])) : date('Y-m-d H:i');
        $orderTotal = number_format((float) ($orderData['total_cost'] ?? $payment['amount']), 2);

        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { border: 1px solid #ddd; border-top: none; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .label { color: #666; width: 40%; }
        .value { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0;">Payment Receipt</h2>
            <p style="margin:6px 0 0 0;">' . APP_NAME . '</p>
        </div>
        <div class="content">
            <p>Thank you, ' . htmlspecialchars($orderData['full_name']) . '. Your payment has been confirmed.</p>
            <table>
                <tr><td class="label">Invoice Number</td><td class="value">' . htmlspecialchars($invoiceNumber) . '</td></tr>
                <tr><td class="label">Order Number</td><td class="value">' . htmlspecialchars($orderData['order_number']) . '</td></tr>
                <tr><td class="label">Transaction ID</td><td class="value">' . htmlspecialchars($payment['provider_payment_intent_id']) . '</td></tr>
                <tr><td class="label">Payment Status</td><td class="value">' . htmlspecialchars(strtoupper($payment['status'])) . '</td></tr>
                <tr><td class="label">Currency</td><td class="value">' . htmlspecialchars($payment['currency']) . '</td></tr>
                <tr><td class="label">Amount Paid</td><td class="value">$' . $amount . '</td></tr>
                <tr><td class="label">Order Total</td><td class="value">$' . $orderTotal . '</td></tr>
                <tr><td class="label">Paid At</td><td class="value">' . htmlspecialchars($paidAt) . '</td></tr>
            </table>
        </div>
    </div>
</body>
</html>';
    }
}
