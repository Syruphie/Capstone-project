<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/EmailMessage.php';

use PHPMailer\PHPMailer\PHPMailer;

class EmailTransportRepository
{
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $fromEmail, string $fromName)
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send(EmailMessage $message): bool
    {
        if (defined('MAIL_USE_SMTP') && MAIL_USE_SMTP) {
            return $this->sendViaSmtp($message);
        }

        return $this->sendViaPhpMail($message);
    }

    private function sendViaPhpMail(EmailMessage $message): bool
    {
        $headers = [];
        $headers[] = 'From: ' . $this->fromName . ' <' . $this->fromEmail . '>';
        $headers[] = 'Reply-To: ' . $this->fromEmail;
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        if ($message->isHtml()) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }

        return mail($message->getTo(), $message->getSubject(), $message->getBody(), implode("\r\n", $headers));
    }

    private function sendViaSmtp(EmailMessage $message): bool
    {
        if (!class_exists(PHPMailer::class)) {
            error_log('PHPMailer not available; falling back to mail()');
            return $this->sendViaPhpMail($message);
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = MAIL_SMTP_HOST;
            $mail->Port = MAIL_SMTP_PORT;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $user = defined('MAIL_SMTP_USER') ? MAIL_SMTP_USER : '';
            $pass = defined('MAIL_SMTP_PASS') ? MAIL_SMTP_PASS : '';
            $mail->SMTPAuth = $user !== '';
            if ($mail->SMTPAuth) {
                $mail->Username = $user;
                $mail->Password = $pass;
            }

            $enc = defined('MAIL_SMTP_ENCRYPTION') ? MAIL_SMTP_ENCRYPTION : '';
            if ($enc === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPAutoTLS = false;
                $mail->SMTPSecure = '';
            }

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($message->getTo());
            $mail->Subject = $message->getSubject();
            $mail->isHTML($message->isHtml());
            $mail->Body = $message->getBody();
            if (!$message->isHtml()) {
                $mail->AltBody = $message->getBody();
            }

            return $mail->send();
        } catch (Throwable $e) {
            error_log('SMTP send failed: ' . $mail->ErrorInfo . ' | ' . $e->getMessage());

            return false;
        }
    }
}
