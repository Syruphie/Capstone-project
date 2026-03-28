<?php
declare(strict_types=1);

class OrderTypeSampleType
{
    public const ORE = 'ore';
    public const LIQUID = 'liquid';

    public static function all(): array
    {
        return [self::ORE, self::LIQUID];
    }

    public static function normalize(string $sampleType): string
    {
        return in_array($sampleType, self::all(), true) ? $sampleType : self::ORE;
    }
}

