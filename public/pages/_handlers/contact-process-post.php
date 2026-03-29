<?php
declare(strict_types=1);

/**
 * Process contact form POST. Same behavior as legacy inline block in contact.php.
 *
 * @return array{error: string, success: string}
 */
function contact_process_post(array $dbUser): array
{
    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return compact('error', 'success');
    }

    $subject = trim($_POST['subject'] ?? '');
    $messageBody = trim($_POST['message'] ?? '');
    $orderNumber = trim($_POST['order_number'] ?? '');

    if ($subject === '' || $messageBody === '') {
        $error = 'Please fill in both subject and message.';
        return compact('error', 'success');
    }

    $emailService = new FrontendEmail();
    $to = defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@example.com';

    $customerName = $dbUser['full_name'] ?? '';
    $customerEmail = $dbUser['email'] ?? '';

    $body = "<p>You have received a new contact message from the customer portal.</p>";
    $body .= "<p><strong>Name:</strong> " . htmlspecialchars($customerName) . "<br>";
    $body .= "<strong>Email:</strong> " . htmlspecialchars($customerEmail) . "</p>";
    if ($orderNumber !== '') {
        $body .= "<p><strong>Related Order #:</strong> " . htmlspecialchars($orderNumber) . "</p>";
    }
    $body .= "<p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>";
    $body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($messageBody)) . "</p>";

    if ($emailService->send($to, '[Contact] ' . $subject, $body, true)) {
        $success = 'Your message has been sent. Our team will get back to you.';
    } else {
        $error = 'Failed to send your message. Please try again later.';
    }

    return compact('error', 'success');
}
