<?php

class NotificationRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insertNotification(
        int $userId,
        int $orderId,
        int $paymentId,
        string $type,
        string $severity,
        string $title,
        string $message
    ): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, order_id, payment_id, notification_type, severity, title, message)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([$userId, $orderId, $paymentId, $type, $severity, $title, $message]);
    }

    public function notifyAdmins(
        int $orderId,
        int $paymentId,
        string $type,
        string $severity,
        string $title,
        string $message
    ): void
    {
        $stmt = $this->db->query("SELECT id FROM users WHERE role = 'administrator' AND is_active = 1");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admins as $admin) {
            $this->insertNotification((int)$admin['id'], $orderId, $paymentId, $type, $severity, $title, $message);
        }
    }
}