<?php
require_once __DIR__ . '/../bootstrap_paths.php';

$user = new FrontendUser();
if (!$user->isLoggedIn()) {
    header('Location: ' . app_path('auth/login.php'));
    exit;
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$customerId = (int)$_SESSION['user_id'];

if ($orderId <= 0) {
    http_response_code(400);
    echo 'Missing order_id';
    exit;
}

$payment = new FrontendPayment();
$invoice = $payment->getInvoiceByOrderAndCustomer($orderId, $customerId);

if (!$invoice) {
    // Backfill invoice after payment sync in case webhook/event timing delayed invoice creation.
    $payment->getPaymentStatusForOrder($orderId, $customerId, true);
    $invoice = $payment->getInvoiceByOrderAndCustomer($orderId, $customerId);
}

if (!$invoice) {
    http_response_code(404);
    echo 'Invoice not found.';
    exit;
}

$invoiceNumber = (string)($invoice['invoice_number'] ?? '');
$receiptHtml = (string)($invoice['receipt_html'] ?? '');
$receiptBody = $receiptHtml;

if (preg_match('/<body[^>]*>(.*)<\/body>/is', $receiptHtml, $matches) === 1) {
    $receiptBody = $matches[1];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php include PAGE_PARTIALS . '/html-base.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($invoiceNumber !== '' ? $invoiceNumber : 'Receipt'); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        :root {
            --page-bg: #f5f7fb;
            --surface: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --border: #e5e7eb;
            --shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        body {
            background: var(--page-bg);
            color: var(--text);
        }

        .invoice-container {
            max-width: 1500px;
            margin: 24px auto;
            padding: 0 16px 24px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
            flex-wrap: wrap;
            background: linear-gradient(180deg, #ffffff, #f9fbff);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 16px 18px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.1;
        }

        .topbar p {
            margin: 6px 0 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .invoice-meta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #dbe6ff;
            background: #edf3ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.2px;
            margin-top: 10px;
        }

        .invoice-shell {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 22px;
            overflow-x: auto;
            width: 100%;
        }

        .receipt-host {
            width: 100%;
        }

        /* Override legacy receipt constraints so content can use available page width. */
        .receipt-host .container {
            max-width: none !important;
            width: max-content !important;
            min-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .receipt-host table {
            width: max-content !important;
            min-width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
        }

        .receipt-host td {
            padding: 12px 14px !important;
            white-space: nowrap !important;
            border-bottom: 1px dotted #cbd5e1;
        }

        .receipt-host tr td:first-child {
            width: 40%;
            color: #4b5563;
        }

        .receipt-host tr:last-child td {
            border-bottom: 0;
        }

        /* Improve the default "Thank you"/intro block from generated receipt HTML. */
        .receipt-host .content > p:first-child {
            margin: 0 0 14px 0;
            padding: 14px 16px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: 600;
        }

        .receipt-host .header {
            padding: 18px 20px !important;
        }

        .receipt-host .content {
            padding: 18px 20px !important;
        }

        .back-link {
            display: inline-block;
            margin-top: 14px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 10px;
            padding: 8px 12px;
            transition: all 0.15s ease;
        }

        .back-link:hover {
            color: #fff;
            background: var(--primary);
            border-color: var(--primary-hover);
        }

        @media (max-width: 768px) {
            .topbar h1 {
                font-size: 24px;
            }

            .invoice-shell {
                padding: 14px;
            }

            .topbar {
                padding: 14px;
            }
        }
    </style>
</head>
<body>
<?php include PAGE_PARTIALS . '/header.php'; ?>

<main class="invoice-container">
    <div class="topbar">
        <div>
            <h1>Invoice</h1>
            <p><?php echo htmlspecialchars($invoiceNumber); ?></p>
            <span class="invoice-meta">Payment Confirmed</span>
        </div>
    </div>

    <section class="invoice-shell">
        <div class="receipt-host">
            <?php echo $receiptBody; ?>
        </div>
    </section>

    <a class="back-link" href="<?php echo htmlspecialchars(app_path('orders/my-orders.php'), ENT_QUOTES, 'UTF-8'); ?>">&larr; Back to My Orders</a>
</main>

<?php include PAGE_PARTIALS . '/footer.php'; ?>
</body>
</html>

