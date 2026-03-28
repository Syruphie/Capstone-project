<?php
declare(strict_types=1);

/**
 * Class PaymentProvider
 *
 * Defines supported payment providers.
 *
 * Responsibilities:
 * - Provide a centralized list of supported payment providers
 *
 * Non-Responsibilities:
 * - No API interaction logic
 *
 * Design Notes:
 * - Currently supports Stripe as the primary provider
 */
class PaymentProvider
{
    public const STRIPE = 'stripe';

    public static function all(): array
    {
        return [
            self::STRIPE,
        ];
    }
}