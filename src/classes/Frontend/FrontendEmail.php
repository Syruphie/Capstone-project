<?php
declare(strict_types=1);

require_once __DIR__ . '/../Email/Service/EmailService.php';

class FrontendEmail
{
    private EmailService $service;

    public function __construct()
    {
        $this->service = new EmailService();
    }

    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool
    {
        return $this->service->send($to, $subject, $body, $isHtml);
    }

    public function sendVerificationPin(string $email, string $name, string $pin): bool
    {
        return $this->service->sendVerificationPin($email, $name, $pin);
    }

    public function sendOrderApprovalNotification(string $customerEmail, string $customerName, string $orderNumber): bool
    {
        return $this->service->sendOrderApprovalNotification($customerEmail, $customerName, $orderNumber);
    }

    public function sendOrderRejectionNotification(string $customerEmail, string $customerName, string $orderNumber, string $reason): bool
    {
        return $this->service->sendOrderRejectionNotification($customerEmail, $customerName, $orderNumber, $reason);
    }

    public function sendOrderScheduleUpdate(string $customerEmail, string $customerName, string $orderNumber, string $newStart, string $newEnd, string $note = ''): bool
    {
        $subject = 'Schedule Update for Order #' . $orderNumber . ' - ' . APP_NAME;
        $body = '<p>Dear ' . htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Your order schedule has been updated.</p>'
            . '<p><strong>Order:</strong> #' . htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8') . '<br>'
            . '<strong>Scheduled Start:</strong> ' . htmlspecialchars($newStart, ENT_QUOTES, 'UTF-8') . '<br>'
            . '<strong>Scheduled End:</strong> ' . htmlspecialchars($newEnd, ENT_QUOTES, 'UTF-8') . '</p>';

        if ($note !== '') {
            $body .= '<p><strong>Note:</strong><br>' . nl2br(htmlspecialchars($note, ENT_QUOTES, 'UTF-8')) . '</p>';
        }

        return $this->service->send($customerEmail, $subject, '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"></head><body>' . $body . '</body></html>', true);
    }

    public function sendOrderCompletedNotification(string $customerEmail, string $customerName, string $orderNumber, string $note = '', ?string $attachmentPath = null, ?string $attachmentName = null): bool
    {
        $subject = 'Results Available for Order #' . $orderNumber . ' - ' . APP_NAME;
        $body = '<p>Dear ' . htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Your order <strong>#' . htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8') . '</strong> is complete and results are available.</p>';

        if ($note !== '') {
            $body .= '<p><strong>Message from lab:</strong><br>' . nl2br(htmlspecialchars($note, ENT_QUOTES, 'UTF-8')) . '</p>';
        }

        if ($attachmentPath !== null && $attachmentName !== null) {
            $body .= '<p><em>Attachment uploaded:</em> ' . htmlspecialchars($attachmentName, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        return $this->service->send($customerEmail, $subject, '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"></head><body>' . $body . '</body></html>', true);
    }
}

