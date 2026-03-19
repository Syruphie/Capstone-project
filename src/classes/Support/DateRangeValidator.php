<?php
declare(strict_types=1);

class DateRangeValidator
{
    /**
     * @throws Exception
     */
    public static function validate(string $startDate, string $endDate): void
    {
        $from = new DateTime($startDate);
        $to = new DateTime($endDate);

        if ($from > $to) {
            throw new InvalidArgumentException("fromDate cannot be greater than toDate: {$startDate} -> {$endDate}");
        }
    }
}