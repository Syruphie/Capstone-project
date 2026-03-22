<?php
declare(strict_types=1);

/**
 * Class SampleStatus
 *
 * Defines allowed statuses for samples.
 *
 * Responsibilities:
 * - Provide a centralized list of valid sample statuses
 *
 * Non-Responsibilities:
 * - No validation logic (handled by validator class)
 *
 * Design Notes:
 * - Used across services and repository to ensure consistency
 */
class SampleStatus
{
    public const PENDING = 'pending';
    public const PREPARING = 'preparing';
    public const READY = 'ready';
    public const TESTING = 'testing';
    public const COMPLETED = 'completed';

    public static function all(): array
    {
        return [
            self::PENDING,
            self::PREPARING,
            self::READY,
            self::TESTING,
            self::COMPLETED,
        ];
    }
}