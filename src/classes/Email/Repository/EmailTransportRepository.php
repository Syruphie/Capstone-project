<?php
declare(strict_types=1);

require_once __DIR__ . '/../Entity/EmailMessage.php';

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
}

