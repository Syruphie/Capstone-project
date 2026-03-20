<?php
declare(strict_types=1);

class DateRangeValidator
{
    /**
     * @throws Exception
     */
    public static function validate(?string $startDate, ?string $endDate): void
    {
        if (!$startDate || !$endDate) {
            throw new InvalidArgumentException("Invalid date range: {$startDate} - {$endDate}");
        }

        $from = new DateTime($startDate);
        $to = new DateTime($endDate);

        if ($from > $to) {
            throw new InvalidArgumentException("fromDate cannot be greater than toDate: {$startDate} -> {$endDate}");
        }
    }
}