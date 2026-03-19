<?php
declare(strict_types=1);

/**
 * Class UserRole
 *
 * Defines the supported user roles for the application.
 *
 * This support class acts as a centralized source of truth for valid
 * user role values, helping to avoid magic strings scattered across the codebase.
 *
 * Responsibilities:
 * - Define supported user role constants
 * - Provide helper methods for validating role values
 * - Centralize role-related shared logic
 *
 * Non-Responsibilities:
 * - No authorization decision workflows
 * - No database access
 * - No session access
 *
 * Design Notes:
 * - Can later be replaced with a native enum if desired
 * - Useful for validation inside services and mappers
 */
final class UserRole
{
    public const CUSTOMER = 'customer';
    public const TECHNICIAN = 'technician';
    public const ADMINISTRATOR = 'administrator';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::CUSTOMER,
            self::TECHNICIAN,
            self::ADMINISTRATOR,
        ];
    }

    public static function isValid(string $role): bool
    {
        return in_array($role, self::all(), true);
    }
}