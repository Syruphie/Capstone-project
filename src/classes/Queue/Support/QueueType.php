<?php
declare(strict_types=1);

/**
 * Class QueueType
 *
 * Defines constants for valid queue types used throughout the system.
 *
 * Responsibilities:
 * - Provide a centralized definition of queue type values
 * - Prevent magic strings and reduce risk of typos
 *
 * Constants:
 * - STANDARD: Standard queue entries
 * - PRIORITY: Priority queue entries
 *
 * Usage:
 * - Used across services, repository, and controllers
 *   instead of hardcoded string values
 */
class QueueType
{
    public const STANDARD = 'standard';
    public const PRIORITY = 'priority';

    public static function isValid(string $queueType): bool
    {
        return in_array($queueType, [self::STANDARD, self::PRIORITY], true);
    }
}
