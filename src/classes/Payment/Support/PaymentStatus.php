<?php
declare(strict_types=1);

/**
 * Class PaymentStatus
 *
 * Defines allowed internal payment statuses.
 *
 * Responsibilities:
 * - Provide a centralized list of valid payment statuses
 *
 * Non-Responsibilities:
 * - No validation or mapping logic
 *
 * Design Notes:
 * - Used across services, entities, and repositories for consistency
 */
class PaymentStatus
{
    public const PENDING = 'pending';
    public const REQUIRES_ACTION = 'requires_action';
    public const SUCCEEDED = 'succeeded';
    public const FAILED = 'failed';
    public const CANCELED = 'canceled';
    public const REFUNDED = 'refunded';

    public static function all(): array
    {
        return [
            self::PENDING,
            self::REQUIRES_ACTION,
            self::SUCCEEDED,
            self::FAILED,
            self::CANCELED,
            self::REFUNDED,
        ];
    }
}