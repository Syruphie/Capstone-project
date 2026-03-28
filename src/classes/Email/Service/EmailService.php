<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/EmailMessage.php';
require_once __DIR__ . '/../Repository/EmailTransportRepository.php';
require_once __DIR__ . '/../Support/EmailTemplate.php';
require_once __DIR__ . '/../Support/EmailTemplateRenderer.php';
require_once __DIR__ . '/../../../../config/database.php';

class EmailService
{
    private EmailTransportRepository $transport;
    private EmailTemplateRenderer $renderer;

    public function __construct(
        ?EmailTransportRepository $transport = null,
        ?EmailTemplateRenderer $renderer = null
    )
    {
        $fromEmail = defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'noreply@globentech.com';
        $fromName = defined('APP_NAME') ? APP_NAME : 'GlobenTech';

        $this->transport = $transport ?? new EmailTransportRepository($fromEmail, $fromName);
        $this->renderer = $renderer ?? new EmailTemplateRenderer();
    }

    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool
    {
        $message = new EmailMessage($to, $subject, $body, $isHtml);

        return $this->transport->send($message);
    }

    public function sendOrderApprovalNotification(string $customerEmail, string $customerName, string $orderNumber): bool
    {
        $subject = 'Your Order #' . $orderNumber . ' Has Been Approved - ' . APP_NAME;

        $body = $this->renderer->render(EmailTemplate::ORDER_APPROVED, [
            'customer_name' => $customerName,
            'order_number' => $orderNumber,
            'app_name' => APP_NAME,
        ]);

        return $this->send($customerEmail, $subject, $body, true);
    }

    public function sendVerificationPin(string $email, string $name, string $pin): bool
    {
        $subject = 'Your Verification Code - ' . APP_NAME;

        $body = $this->renderer->render(EmailTemplate::VERIFICATION_PIN, [
            'name' => $name,
            'pin' => $pin,
            'app_name' => APP_NAME,
        ]);

        return $this->send($email, $subject, $body, true);
    }

    public function sendOrderRejectionNotification(
        string $customerEmail,
        string $customerName,
        string $orderNumber,
        string $reason
    ): bool
    {
        $subject = 'Your Order #' . $orderNumber . ' Status Update - ' . APP_NAME;

        $body = $this->renderer->render(EmailTemplate::ORDER_REJECTED, [
            'customer_name' => $customerName,
            'order_number' => $orderNumber,
            'rejection_reason' => $reason,
            'app_name' => APP_NAME,
        ]);

        return $this->send($customerEmail, $subject, $body, true);
    }
}

