<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('InvoiceService');

class FakePaymentReceiptService extends PaymentReceiptService
{
    public function __construct()
    {
        $paymentRepository = makePaymentRepository();
        $invoiceRepository = makeInvoiceRepository();
        $invoiceService = new InvoiceService($invoiceRepository, $paymentRepository);

        parent::__construct($paymentRepository, $invoiceService, $invoiceRepository);
    }

    public function buildReceiptHtml(string $invoiceNumber, array $payment, array $orderData): string
    {
        return '<html lang="en"><body>Receipt ' . $invoiceNumber . '</body></html>';
    }

    public function emailReceiptForPayment(int $paymentId): bool
    {
        return true;
    }
}

$db = getTestDb();
$paymentRepository = makePaymentRepository();
$invoiceRepository = makeInvoiceRepository();
$invoiceService = new InvoiceService($invoiceRepository, $paymentRepository);
$receiptService = new FakePaymentReceiptService();

$paymentRepository->upsertFromProviderData([
    'id' => 'pi_invoice_success_001',
    'amount' => 8800,
    'currency' => 'cad',
    'status' => 'succeeded',
    'payment_method_types' => ['card'],
], 1, 1);

$paymentId = $paymentRepository->getPaymentIdByIntent('pi_invoice_success_001');
assertNotNull($paymentId, 'succeeded payment should exist before invoice generation');

$generated = $invoiceService->generateInvoiceForPayment((int)$paymentId, $receiptService);
assertTrue($generated, 'generateInvoiceForPayment should succeed for succeeded payments');

$invoice = $invoiceRepository->getByPaymentId((int)$paymentId);
assertNotNull($invoice, 'invoice row should be created for succeeded payment');
assertSame((int)$paymentId, (int)$invoice['payment_id'], 'invoice should reference payment id');
assertTrue(str_starts_with($invoice['invoice_number'], 'INV-'), 'invoice number should use INV- prefix');
printPass('generateInvoiceForPayment creates invoice for succeeded payment');

$generatedAgain = $invoiceService->generateInvoiceForPayment((int)$paymentId, $receiptService);
assertTrue($generatedAgain, 'second generate call should be idempotent for existing invoice');

$stmt = $db->prepare('SELECT COUNT(*) AS c FROM invoices WHERE payment_id = ?');
$stmt->execute([(int)$paymentId]);
$countRow = $stmt->fetch(PDO::FETCH_ASSOC);
assertSame(1, (int)$countRow['c'], 'idempotent invoice generation should not duplicate invoices');
printPass('generateInvoiceForPayment is idempotent for same payment');

require __DIR__ . '/../reset_test_db.php';

$paymentRepository = makePaymentRepository();
$invoiceRepository = makeInvoiceRepository();
$invoiceService = new InvoiceService($invoiceRepository, $paymentRepository);

$paymentRepository->upsertFromProviderData([
    'id' => 'pi_invoice_pending_001',
    'amount' => 4500,
    'currency' => 'cad',
    'status' => 'requires_action',
    'payment_method_types' => ['card'],
], 1, 1);

$pendingPaymentId = $paymentRepository->getPaymentIdByIntent('pi_invoice_pending_001');
assertNotNull($pendingPaymentId, 'pending payment should exist before invoice generation');

$notGenerated = $invoiceService->generateInvoiceForPayment((int)$pendingPaymentId, $receiptService);
assertFalse($notGenerated, 'generateInvoiceForPayment should reject non-succeeded payments');
assertNull($invoiceRepository->getByPaymentId((int)$pendingPaymentId), 'no invoice should be created for non-succeeded payment');
printPass('generateInvoiceForPayment rejects non-succeeded payments');

