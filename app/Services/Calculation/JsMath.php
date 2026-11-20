<?php

namespace App\Services\Calculation;

/**
 * JavaScript number semantics the legacy engine relies on (BRD FR-027).
 */
final class JsMath
{
    /**
     * Number(fieldValue): empty fields become 0, non-numeric text becomes NaN.
     */
    public static function number(mixed $value): float
    {
        if ($value === null || $value === '' || $value === false) {
            return 0.0;
        }

        if ($value === true) {
            return 1.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return 0.0;
        }

        return is_numeric($trimmed) ? (float) $trimmed : NAN;
    }

    /**
     * Math.round(): halves round towards positive infinity.
     */
    public static function round(float $value): float
    {
        $floor = floor($value);

        return $value - $floor >= 0.5 ? $floor + 1 : $floor;
    }

    /**
     * Number(value.toFixed(0)) for the non-negative instalments the engine rounds (BRD Q-01).
     */
    public static function toFixedZero(float $value): float
    {
        return self::round($value);
    }

    /**
     * The text of a form field, as the legacy code reads it with .val().
     */
    public static function text(mixed $value): string
    {
        return $value === null ? '' : (string) $value;
    }
}
