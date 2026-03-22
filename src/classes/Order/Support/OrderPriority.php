<?php

class OrderPriority
{
    public const STANDARD = 'standard';
    public const PRIORITY = 'priority';

    public static function all(): array
    {
        return [
            self::STANDARD,
            self::PRIORITY,
        ];
    }

    public static function isValid(string $priority): bool
    {
        return in_array($priority, self::all());
    }
}