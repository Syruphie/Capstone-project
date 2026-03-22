<?php

class ValidateOrderStatus
{
    public static function validate(string $status): void
    {
        if (!in_array($status, OrderStatus::all())) {
            throw new InvalidArgumentException("Invalid status: $status");
        }
    }
}