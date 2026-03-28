<?php
declare(strict_types=1);

/**
 * Class PaymentNotificationService
 *
 * Handles payment-related notification orchestration.
 *
 * Responsibilities:
 * - Create customer-facing payment notifications
 * - Create admin-facing alerts for notable payment events
 *
 * Non-Responsibilities:
 * - No provider API calls
 * - No payment state calculation
 *
 * Design Notes:
 * - Centralizes payment notification decisions and wording
 */

require_once __DIR__ . '/../Repository/NotificationRepository.php';

class PaymentNotificationService
{
    private NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotificationsForPaymentState(
        ?int $paymentId,
        int $orderId,
        int $customerId,
        string $status,
        float $amount,
        ?string $failureReason
    ): void
    {
        if (!$paymentId) {
            return;
        }

        if ($status === 'succeeded') {
            $this->notificationRepository->insertNotification(
                $customerId,
                $orderId,
                $paymentId,
                'payment_confirmation',
                'info',
                'Payment confirmed',
                'Your payment was confirmed and your order is now progressing.'
            );

            if ($amount >= 1000) {
                $this->notificationRepository->notifyAdmins(
                    $orderId,
                    $paymentId,
                    'high_value_payment',
                    'warning',
                    'High-value payment received',
                    'A high-value payment was received and should be reviewed by an administrator.'
                );
            }
        }

        if (in_array($status, ['requires_payment_method', 'failed', 'canceled'], true)) {
            $this->notificationRepository->insertNotification(
                $customerId,
                $orderId,
                $paymentId,
                'payment_failure',
                'warning',
                'Payment requires attention',
                'Payment failed or needs a retry. Please update your payment method and try again.'
            );

            $message = strtolower((string)$failureReason);
            if (strpos($message, 'fraud') !== false || strpos($message, 'do not honor') !== false) {
                $this->notificationRepository->notifyAdmins(
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
}