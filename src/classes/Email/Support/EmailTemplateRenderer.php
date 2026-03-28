<?php
declare(strict_types=1);

require_once __DIR__ . '/EmailTemplate.php';

class EmailTemplateRenderer
{
    public function render(string $templateName, array $variables): string
    {
        $template = $this->getTemplate($templateName);

        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'), $template);
        }

        return $template;
    }

    private function getTemplate(string $templateName): string
    {
        $templates = [
            EmailTemplate::ORDER_APPROVED => '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;">
    <h2>Order Approved</h2>
    <p>Dear {{customer_name}},</p>
    <p>Your order <strong>#{{order_number}}</strong> has been approved and is now being processed.</p>
    <p>Thank you for choosing {{app_name}}.</p>
</body>
</html>',
            EmailTemplate::ORDER_REJECTED => '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;">
    <h2>Order Status Update</h2>
    <p>Dear {{customer_name}},</p>
    <p>Your order <strong>#{{order_number}}</strong> could not be approved at this time.</p>
    <p><strong>Reason:</strong> {{rejection_reason}}</p>
    <p>Please contact support if you need help.</p>
</body>
</html>',
            EmailTemplate::VERIFICATION_PIN => '
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;">
    <h2>Email Verification</h2>
    <p>Dear {{name}},</p>
    <p>Use the following PIN to verify your email for {{app_name}}:</p>
    <p style="font-size:28px;letter-spacing:6px;"><strong>{{pin}}</strong></p>
    <p>This code expires in 10 minutes.</p>
</body>
</html>',
        ];

        return $templates[$templateName] ?? '';
    }
}

