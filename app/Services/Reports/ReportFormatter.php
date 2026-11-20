<?php

namespace App\Services\Reports;

/**
 * Number formatting for the report: Latin digits with thousands separators
 * and the study's chosen decimals, matching resources/js/lib/format.ts.
 */
final readonly class ReportFormatter
{
    public function __construct(public int $decimals) {}

    public function money(int|float|string|null $value): string
    {
        return $this->number($value, $this->decimals);
    }

    public function number(int|float|string|null $value, int $decimals = 0): string
    {
        $number = (float) ($value ?? 0);

        // Avoid printing "-0.00" for tiny negative rounding leftovers.
        if (round($number, $decimals) == 0) {
            $number = 0.0;
        }

        return number_format($number, $decimals, '.', ',');
    }

    public function percent(int|float|string|null $value, int $decimals = 1): string
    {
        return $value === null ? '—' : $this->number($value, $decimals).'%';
    }
}
