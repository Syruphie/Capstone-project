<?php
require_once 'stripe_env_loader.php';
require_once __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');


$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';

if (!$email) {
    echo json_encode(['success' => false, 'error' => 'Missing email']);
    exit;
}

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => 2000, // $20.00 in cents
        'currency' => 'cad',
        'automatic_payment_methods' => [
            'enabled' => true,
            'allow_redirects' => 'never'
        ],
        'receipt_email' => $email,
    ]);
    echo json_encode([
        'success' => true,
        'client_secret' => $paymentIntent->client_secret
    ]);
} catch (\Stripe\Exception\CardException $e) {
    echo json_encode(['success' => false, 'error' => $e->getError()->message]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
