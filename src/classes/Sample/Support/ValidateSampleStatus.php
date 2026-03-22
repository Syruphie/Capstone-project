<?php
declare(strict_types=1);

/**
 * Class ValidateSampleStatus
 *
 * Validates sample status values.
 *
 * Responsibilities:
 * - Ensure a given status is valid before persistence or transition
 *
 * Non-Responsibilities:
 * - No state mutation or database interaction
 *
 * Design Notes:
 * - Throws exceptions on invalid input to prevent bad state
 */
class ValidateSampleStatus
{
    public static function validate(string $status): void
    {
        if (!in_array($status, SampleStatus::all(), true)) {
            throw new InvalidArgumentException("Invalid sample status: {$status}");
        }
    }
}