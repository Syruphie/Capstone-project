<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

printSection('EmailService');

class FakeEmailTransportRepository extends EmailTransportRepository
{
    public array $messages = [];

    public function __construct()
    {
        parent::__construct('noreply@test.local', 'Test Sender');
    }

    public function send(EmailMessage $message): bool
    {
        $this->messages[] = $message;

        return true;
    }
}

$transport = new FakeEmailTransportRepository();
$renderer = new EmailTemplateRenderer();
$service = new EmailService($transport, $renderer);

assertTrue($service->send('person@test.com', 'Subject', 'Body', false), 'send should delegate message to transport');
assertCountSame(1, $transport->messages, 'send should push one outbound message');
assertSame('person@test.com', $transport->messages[0]->getTo(), 'message recipient should match');
assertFalse($transport->messages[0]->isHtml(), 'message html flag should match call input');
printPass('send delegates to transport');

$approvalOk = $service->sendOrderApprovalNotification('customer@test.com', 'Jane Customer', 'ORD-ABC-001');
assertTrue($approvalOk, 'sendOrderApprovalNotification should succeed through transport');

$approvalMessage = $transport->messages[1];
assertTrue(str_contains($approvalMessage->getSubject(), 'ORD-ABC-001'), 'approval subject should include order number');
assertTrue(str_contains($approvalMessage->getBody(), 'Jane Customer'), 'approval body should include customer name');
assertTrue(str_contains($approvalMessage->getBody(), APP_NAME), 'approval body should include app name');
printPass('sendOrderApprovalNotification composes expected email');

$pinOk = $service->sendVerificationPin('verify@test.com', 'Alice Verify', '739201');
assertTrue($pinOk, 'sendVerificationPin should succeed through transport');
$pinMessage = $transport->messages[2];
assertTrue(str_contains($pinMessage->getBody(), '739201'), 'verification email should include pin');
printPass('sendVerificationPin composes expected email');

$rejectOk = $service->sendOrderRejectionNotification(
    'customer@test.com',
    '<Admin>',
    'ORD-ABC-002',
    'Missing documentation'
);
assertTrue($rejectOk, 'sendOrderRejectionNotification should succeed through transport');
$rejectMessage = $transport->messages[3];
assertTrue(str_contains($rejectMessage->getBody(), '&lt;Admin&gt;'), 'template rendering should escape HTML in variables');
assertTrue(str_contains($rejectMessage->getBody(), 'Missing documentation'), 'rejection body should include reason');
printPass('sendOrderRejectionNotification composes and escapes values');

