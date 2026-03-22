<?php
declare(strict_types=1);

/**
 * Class SampleType
 *
 * Defines allowed sample types.
 *
 * Responsibilities:
 * - Provide a centralized list of valid sample types
 *
 * Non-Responsibilities:
 * - No validation or business logic
 *
 * Design Notes:
 * - Used for preparation logic and type enforcement
 */
class SampleType
{
    public const ORE = 'ore';
    public const LIQUID = 'liquid';

    public static function all(): array
    {
        return [
            self::ORE,
            self::LIQUID,
        ];
    }
}