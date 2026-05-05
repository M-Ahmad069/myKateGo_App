<?php

namespace App\Enums;

enum MealType: string
{
    case Breakfast = 'breakfast';
    case SnackAm = 'snack_am';
    case Lunch = 'lunch';
    case SnackPm = 'snack_pm';
    case Dinner = 'dinner';

    public static function fromAiType(string $raw): self
    {
        $n = strtolower(trim(str_replace(['-', ' '], '_', $raw)));

        if (str_contains($n, 'breakfast')) {
            return self::Breakfast;
        }
        if (str_contains($n, 'lunch')) {
            return self::Lunch;
        }
        if (str_contains($n, 'dinner') || str_contains($n, 'supper')) {
            return self::Dinner;
        }
        if (str_contains($n, 'snack_pm') || str_contains($n, 'afternoon') || str_contains($n, 'evening')) {
            return self::SnackPm;
        }
        if (str_contains($n, 'snack') || str_contains($n, 'morning')) {
            return self::SnackAm;
        }

        $direct = self::tryFrom($n);

        return $direct ?? self::Lunch;
    }
}
