<?php
declare(strict_types=1);

/**
 * Class ValidateSampleType
 *
 * Validates sample type values.
 *
 * Responsibilities:
 * - Ensure a given sample type is valid
 *
 * Non-Responsibilities:
 * - No business logic or persistence
 *
 * Design Notes:
 * - Used by services before applying type-based logic
 */
class ValidateSampleType
{
    public static function validate(string $sampleType): void
    {
        if (!in_array($sampleType, SampleType::all(), true)) {
            throw new InvalidArgumentException("Invalid sample type: {$sampleType}");
        }
    }
}