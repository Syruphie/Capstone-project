<?php
require_once __DIR__ . '/../config/database.php';

class Email {
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->fromEmail = 'noreply@globentech.com';
        $this->fromName = APP_NAME;
    }

    /**
     * Send an email using PHP mail function
     * For local development, use MailHog (see read-me/Local email setup.md)
     */
    public function send($to, $subject, $body, $isHtml = true) {
        $headers = array();
        $headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $headers[] = "Reply-To: {$this->fromEmail}";
        $headers[] = "X-Mailer: PHP/" . phpversion();

        if ($isHtml) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        }

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Send order approval notification to customer
     */
    public function sendOrderApprovalNotification($customerEmail, $customerName, $orderNumber) {
        $subject = "Your Order #{$orderNumber} Has Been Approved - " . APP_NAME;

        $body = $this->getEmailTemplate('order_approved', [
            'customer_name' => $customerName,
            'order_number' => $orderNumber,
            'app_name' => APP_NAME
        ]);

        return $this->send($customerEmail, $subject, $body);
    }

    /**
     * Send email verification PIN
     */
    public function sendVerificationPin($email, $name, $pin) {
        $subject = "Your Verification Code - " . APP_NAME;

        $body = $this->getEmailTemplate('verification_pin', [
            'name' => $name,
            'pin' => $pin,
            'app_name' => APP_NAME
        ]);

        return $this->send($email, $subject, $body);
    }

    /**
     * Send order rejection notification to customer
     */
    public function sendOrderRejectionNotification($customerEmail, $customerName, $orderNumber, $reason) {
        $subject = "Your Order #{$orderNumber} Status Update - " . APP_NAME;

        $body = $this->getEmailTemplate('order_rejected', [
            'customer_name' => $customerName,
            'order_number' => $orderNumber,
            'rejection_reason' => $reason,
            'app_name' => APP_NAME
        ]);

        return $this->send($customerEmail, $subject, $body);
    }

    /**
     * Get email template with variable substitution
     */
    private function getEmailTemplate($templateName, $variables) {
        $templates = [
            'order_approved' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px; }
        .status-badge { display: inline-block; background: #28a745; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .order-number { font-size: 24px; color: #667eea; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{app_name}}</h1>
        </div>
        <div class="content">
            <h2>Order Approved!</h2>
            <p>Dear {{customer_name}},</p>
            <p>Great news! Your order has been approved and is now being processed.</p>
            <p><strong>Order Number:</strong> <span class="order-number">{{order_number}}</span></p>
            <p><span class="status-badge">APPROVED</span></p>
            <p>Your samples will be prepared and tested according to our standard laboratory procedures. You will receive updates as your order progresses through our system.</p>
            <p>If you have any questions, please do not hesitate to contact us.</p>
            <p>Thank you for choosing {{app_name}}!</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {{app_name}}. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>',

            'order_rejected' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px; }
        .status-badge { display: inline-block; background: #dc3545; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .order-number { font-size: 24px; color: #667eea; font-weight: bold; }
        .reason-box { background: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{app_name}}</h1>
        </div>
        <div class="content">
            <h2>Order Status Update</h2>
            <p>Dear {{customer_name}},</p>
            <p>We regret to inform you that your order could not be approved at this time.</p>
            <p><strong>Order Number:</strong> <span class="order-number">{{order_number}}</span></p>
            <p><span class="status-badge">NOT APPROVED</span></p>
            <div class="reason-box">
                <strong>Reason:</strong><br>
                {{rejection_reason}}
            </div>
            <p>If you believe this was in error or would like to discuss this further, please contact our support team.</p>
            <p>Thank you for your understanding.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {{app_name}}. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>',

            'verification_pin' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px; }
        .pin-code { font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 8px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{app_name}}</h1>
        </div>
        <div class="content">
            <h2>Email Verification</h2>
            <p>Dear {{name}},</p>
            <p>Thank you for registering with {{app_name}}. Please use the following PIN to verify your email address:</p>
            <div class="pin-code">{{pin}}</div>
            <p>This code will expire in 10 minutes.</p>
            <p>If you did not request this verification, please ignore this email.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {{app_name}}. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>'
        ];

        $template = $templates[$templateName] ?? '';

        // Replace variables
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars($value), $template);
        }

        return $template;
    }
}
