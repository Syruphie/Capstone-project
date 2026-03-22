<?php
declare(strict_types=1);

/**
 * Class OrderStatus
 *
 * Defines valid order statuses for the order domain.
 *
 * Responsibilities:
 * - Provide a centralized list of valid statuses
 * - Validate status values
 *
 * Non-Responsibilities:
 * - No persistence
 * - No orchestration
 * - No UI formatting
 */
class OrderStatus
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const PAYMENT_CONFIRMED = 'payment_confirmed';
    public const IN_QUEUE = 'in_queue';
    public const PREPARATION_IN_PROGRESS = 'preparation_in_progress';
    public const TESTING_IN_PROGRESS = 'testing_in_progress';
    public const RESULTS_AVAILABLE = 'results_available';
    public const COMPLETED = 'completed';

    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::APPROVED,
            self::REJECTED,
            self::PAYMENT_CONFIRMED,
            self::IN_QUEUE,
            self::PREPARATION_IN_PROGRESS,
            self::TESTING_IN_PROGRESS,
            self::RESULTS_AVAILABLE,
            self::COMPLETED,
        ];
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }

    public static function isFinished(string $status): bool
    {
        return in_array($status, [self::RESULTS_AVAILABLE, self::COMPLETED], true);
    }

    public static function countsAsRevenue(string $status): bool
    {
        return in_array($status, [
            self::PAYMENT_CONFIRMED,
            self::IN_QUEUE,
            self::PREPARATION_IN_PROGRESS,
            self::TESTING_IN_PROGRESS,
            self::RESULTS_AVAILABLE,
            self::COMPLETED,
        ], true);
    }
}