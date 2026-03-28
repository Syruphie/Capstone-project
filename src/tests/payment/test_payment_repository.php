<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('PaymentRepository');

$repo = makePaymentRepository();

$inserted = $repo->upsertFromProviderData([
    'id' => 'pi_test_repo_001',
    'amount' => 12345,
    'currency' => 'cad',
    'status' => 'requires_action',
    'payment_method_types' => ['card'],
], 1, 1);

assertTrue($inserted, 'upsertFromProviderData should insert a payment row');
printPass('upsertFromProviderData inserts payment row');

$paymentId = $repo->getPaymentIdByIntent('pi_test_repo_001');
assertNotNull($paymentId, 'getPaymentIdByIntent should return inserted payment id');
printPass('getPaymentIdByIntent returns id');

$payment = $repo->getById((int)$paymentId);
assertNotNull($payment, 'getById should return inserted payment entity');
assertSame('requires_action', $payment->getStatus(), 'inserted status should match provider status');
assertSame('CAD', $payment->getCurrency(), 'currency should be normalized to uppercase');
assertTrue(abs($payment->getAmount() - 123.45) < 0.0001, 'amount should be converted from cents');
assertSame('pi_test_repo_001', $payment->getProviderPaymentIntentId(), 'provider intent id should match');
printPass('inserted payment fields are mapped correctly');

$updated = $repo->upsertFromProviderData([
    'id' => 'pi_test_repo_001',
    'amount' => 20000,
    'currency' => 'usd',
    'status' => 'succeeded',
    'payment_method_types' => ['card'],
], 1, 1);

assertTrue($updated, 'upsertFromProviderData should update duplicate provider intent');

$samePaymentId = $repo->getPaymentIdByIntent('pi_test_repo_001');
assertSame($paymentId, $samePaymentId, 'duplicate intent upsert should keep same payment id');

$updatedPayment = $repo->getById((int)$samePaymentId);
assertSame('succeeded', $updatedPayment->getStatus(), 'status should update to succeeded');
assertSame('USD', $updatedPayment->getCurrency(), 'updated currency should be normalized');
assertTrue(abs($updatedPayment->getAmount() - 200.0) < 0.0001, 'updated amount should reflect latest intent');
assertNotNull($updatedPayment->getPaidAt(), 'paid_at should be set for succeeded status');
printPass('duplicate provider intent upserts existing payment row');

assertNull($repo->getPaymentIdByIntent(''), 'empty intent id should return null');
printPass('getPaymentIdByIntent returns null for empty id');

$invalid = $repo->upsertFromProviderData(['id' => 'pi_invalid'], 0, 1);
assertFalse($invalid, 'upsertFromProviderData should reject non-positive order id');
printPass('upsertFromProviderData validates order and customer ids');

