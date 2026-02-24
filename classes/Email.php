<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email {
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $this->fromEmail = 'noreply@globentech.com';
        $this->fromName = APP_NAME;
    }

    /**
     * Send an email. Uses SMTP (Mailpit) when MAIL_USE_SMTP is true, else PHP mail().
     */
    public function send($to, $subject, $body, $isHtml = true) {
        if (defined('MAIL_USE_SMTP') && MAIL_USE_SMTP) {
            return $this->sendViaSmtp($to, $subject, $body, $isHtml);
        }
        $headers = [];
        $headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        $headers[] = "Reply-To: {$this->fromEmail}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        if ($isHtml) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        }
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    private function sendViaSmtp($to, $subject, $body, $isHtml) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : '127.0.0.1';
            $mail->Port = defined('MAIL_SMTP_PORT') ? (int) MAIL_SMTP_PORT : 1025;
            $mail->SMTPAuth = false;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML($isHtml);
            $mail->Body = $body;
            if (!$isHtml) {
                $mail->AltBody = $body;
            }
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Email send failed: ' . $mail->ErrorInfo);
            return false;
        }
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
     * Send order completion notification to customer, with optional message and attachment.
     */
    public function sendOrderCompletedNotification($customerEmail, $customerName, $orderNumber, $customMessage = '', $attachmentPath = null, $attachmentName = null) {
        $defaultMessage = 'Your order analysis is complete and results are now available in your customer portal. If you have any problems or questions, please use our Contact Us page.';
        $customText = $customMessage !== '' && $customMessage !== null ? ('Additional note from our team: ' . $customMessage) : $defaultMessage;

        $subject = "Your Order #{$orderNumber} Analysis Is Complete - " . APP_NAME;

        $body = $this->getEmailTemplate('order_completed', [
            'customer_name' => $customerName,
            'order_number' => $orderNumber,
            'custom_message_plain' => $customText,
            'app_name' => APP_NAME,
            'contact_url' => BASE_URL . '/contact.php'
        ]);

        // If we have an attachment and SMTP is enabled, send via PHPMailer directly
        if ($attachmentPath && is_readable($attachmentPath) && defined('MAIL_USE_SMTP') && MAIL_USE_SMTP) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = defined('MAIL_SMTP_HOST') ? MAIL_SMTP_HOST : '127.0.0.1';
                $mail->Port = defined('MAIL_SMTP_PORT') ? (int) MAIL_SMTP_PORT : 1025;
                $mail->SMTPAuth = false;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress($customerEmail, $customerName);
                $mail->Subject = $subject;
                $mail->isHTML(true);
                $mail->Body = $body;
                $mail->addAttachment($attachmentPath, $attachmentName ?: basename($attachmentPath));
                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log('Order completion email failed: ' . $mail->ErrorInfo);
                // Fall through to basic send without attachment
            }
        }

        // Fallback: send without attachment using existing send()
        return $this->send($customerEmail, $subject, $body);
    }

    /**
     * Send order schedule update notification to customer
     */
    public function sendOrderScheduleUpdate($customerEmail, $customerName, $orderNumber, $newStart, $newEnd, $customMessage = '') {
        $subject = "Your Order #{$orderNumber} Schedule Has Been Updated - " . APP_NAME;

        $body = $this->getEmailTemplate('order_schedule_updated', [
            'customer_name' => $customerName,
            'order_number' => $orderNumber,
            'new_start' => $newStart,
            'new_end' => $newEnd,
            'app_name' => APP_NAME,
            'contact_url' => BASE_URL . '/contact.php',
            'custom_message_plain' => $customMessage !== '' ? ('Additional note from our team: ' . $customMessage) : ''
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
</html>',

            'order_schedule_updated' => '
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
        .order-number { font-size: 24px; color: #667eea; font-weight: bold; }
        .schedule-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{app_name}}</h1>
        </div>
        <div class="content">
            <h2>Order Schedule Updated</h2>
            <p>Dear {{customer_name}},</p>
            <p>Due to internal scheduling adjustments, the expected timing for your order has changed.</p>
            <p><strong>Order Number:</strong> <span class="order-number">{{order_number}}</span></p>
            <div class="schedule-box">
                <p><strong>New scheduled start:</strong> {{new_start}}</p>
                <p><strong>New scheduled completion:</strong> {{new_end}}</p>
            </div>
            <p>If you would like to discuss this change, please contact us through your customer portal:</p>
            <p><a href="{{contact_url}}" style="color:#667eea; font-weight:bold;">Contact Us</a></p>
            <p>{{custom_message_plain}}</p>
            <p>Thank you for choosing {{app_name}}.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from {{app_name}}. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>',

            'order_completed' => '
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
        .order-number { font-size: 24px; color: #667eea; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{app_name}}</h1>
        </div>
        <div class="content">
            <h2>Order Analysis Complete</h2>
            <p>Dear {{customer_name}},</p>
            <p>Your order analysis is now complete.</p>
            <p><strong>Order Number:</strong> <span class="order-number">{{order_number}}</span></p>
            <p>{{custom_message_plain}}</p>
            <p>If you have any problems or questions about your results, please visit our Contact Us page:</p>
            <p><a href="{{contact_url}}" style="color:#667eea; font-weight:bold;">Contact Us</a></p>
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
