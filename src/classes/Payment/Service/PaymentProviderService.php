<?php
declare(strict_types=1);

/**
 * Class PaymentProviderService
 *
 * Handles direct interaction with the external payment provider.
 *
 * Responsibilities:
 * - Create payment intents with Stripe
 * - Retrieve provider-side payment intent data
 * - Construct provider webhook events from incoming payloads
 *
 * Non-Responsibilities:
 * - No database persistence
 * - No order status updates
 * - No notification orchestration
 *
 * Design Notes:
 * - Encapsulates all Stripe SDK usage
 * - Keeps provider-specific logic out of repositories and domain entities
 */

use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;

require_once __DIR__ . '/../../../config/stripe_env_loader.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../Support/EmailValidator.php';

class PaymentProviderService
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? '');
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentIntent(
        int $orderId,
        int $customerId,
        string $email,
        float $amount,
        string $currency = 'cad'
    ): Stripe\PaymentIntent
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than 0');
        }

        EmailValidator::validateEmail($email);

        $amountCents = (int)round($amount * 100);

        return \Stripe\PaymentIntent::create([
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never'
            ],
            'receipt_email' => $email,
            'metadata' => [
                'order_id' => (string)$orderId,
                'customer_id' => (string)$customerId
            ]
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $providerPaymentIntentId): Stripe\PaymentIntent
    {
        return \Stripe\PaymentIntent::retrieve($providerPaymentIntentId);
    }

    /**
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, ?string $signatureHeader = null): Stripe\Event
    {
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        if (!empty($webhookSecret) && $signatureHeader) {
            return \Stripe\Webhook::constructEvent($payload, $signatureHeader, $webhookSecret);
        }

        return \Stripe\Event::constructFrom(json_decode($payload, true));
    }
}