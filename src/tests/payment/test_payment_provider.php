<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

printSection('PaymentProvider');

$providers = PaymentProvider::all();
assertTrue(in_array(PaymentProvider::STRIPE, $providers, true), 'supported providers should include stripe');
assertCountSame(1, $providers, 'current provider list should contain one provider');
printPass('PaymentProvider exposes expected provider list');

