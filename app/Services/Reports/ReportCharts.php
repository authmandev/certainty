<?php

namespace App\Services\Reports;

/**
 * Static SVG charts for the PDF, drawn on the server so the report needs no
 * JavaScript. Time axes run right to left, like the editor's charts.
 */
final class ReportCharts
{
    /**
     * A line chart with one line per series and year one on the right.
     *
     * @param  list<string>  $labels
     * @param  list<array{label: string, color: string, values: list<float|int>, dashed?: bool}>  $series
     */
    public static function lines(array $labels, array $series, int $width = 680, int $height = 260): string
    {
        $padding = ['top' => 16, 'bottom' => 30, 'start' => 12, 'end' => 70];
        $values = array_merge(...array_map(fn (array $item): array => $item['values'], $series));
        $max = max(0, ...$values);
        $min = min(0, ...$values);
        $span = ($max - $min) ?: 1;
        $max += $span * 0.08;
        $min = $min < 0 ? $min - $span * 0.08 : 0;

        $chartHeight = $height - $padding['top'] - $padding['bottom'];
        $count = count($labels);
        $step = ($width - $padding['start'] - $padding['end']) / max($count - 1, 1);
        $x = fn (int $index): float => $count === 1
            ? ($width - $padding['end'] + $padding['start']) / 2
            : $width - $padding['end'] - $index * $step;
        $y = fn (float|int $value): float => $padding['top'] + (($max - $value) / ($max - $min)) * $chartHeight;

        $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" class="chart" style="direction:ltr">', $width, $height);

        foreach ([0, 0.25, 0.5, 0.75, 1] as $ratio) {
            $value = $min + ($max - $min) * $ratio;
            $svg .= sprintf(
                '<line x1="%d" x2="%d" y1="%.1F" y2="%.1F" stroke="#94a3b8" stroke-opacity="0.35" stroke-dasharray="3 5"/><text x="%d" y="%.1F" font-size="10" fill="#64748b">%s</text>',
                $padding['start'], $width - $padding['end'], $y($value), $y($value), $width - $padding['end'] + 8, $y($value) + 3.5, self::compact($value),
            );
        }

        if ($min < 0) {
            $svg .= sprintf('<line x1="%d" x2="%d" y1="%.1F" y2="%.1F" stroke="#475569" stroke-opacity="0.6"/>', $padding['start'], $width - $padding['end'], $y(0), $y(0));
        }

        foreach ($labels as $index => $label) {
            $svg .= sprintf('<text x="%.1F" y="%d" font-size="10" fill="#64748b" text-anchor="middle">%s</text>', $x($index), $height - 8, e($label));
        }

        foreach ($series as $item) {
            $points = [];

            foreach ($item['values'] as $index => $value) {
                $points[] = sprintf('%.1F,%.1F', $x($index), $y($value));
            }

            $svg .= sprintf(
                '<polyline points="%s" fill="none" stroke="%s" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"%s/>',
                implode(' ', $points), $item['color'], ($item['dashed'] ?? false) ? ' stroke-dasharray="6 4"' : '',
            );

            foreach ($item['values'] as $index => $value) {
                $svg .= sprintf('<circle cx="%.1F" cy="%.1F" r="2.8" fill="%s"/>', $x($index), $y($value), $item['color']);
            }
        }

        return $svg.'</svg>';
    }

    /**
     * A donut chart. Segments with no value are skipped.
     *
     * @param  list<array{label: string, color: string, value: float|int}>  $segments
     */
    public static function donut(array $segments, int $size = 150, int $thickness = 26): string
    {
        $segments = array_values(array_filter($segments, fn (array $segment): bool => $segment['value'] > 0));
        $total = array_sum(array_column($segments, 'value'));
        $radius = ($size - $thickness) / 2;
        $center = $size / 2;

        $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" width="%1$d" height="%1$d">', $size);
        $svg .= sprintf('<circle cx="%1$.1F" cy="%1$.1F" r="%2$.1F" fill="none" stroke="#e2e8f0" stroke-width="%3$d"/>', $center, $radius, $thickness);

        $offset = 0.0;

        foreach ($segments as $segment) {
            $share = $total > 0 ? $segment['value'] / $total * 100 : 0;
            $svg .= sprintf(
                '<circle cx="%1$.1F" cy="%1$.1F" r="%2$.1F" fill="none" stroke="%3$s" stroke-width="%4$d" pathLength="100" stroke-dasharray="%5$.3F %6$.3F" stroke-dashoffset="%7$.3F" transform="rotate(-90 %1$.1F %1$.1F)"/>',
                $center, $radius, $segment['color'], $thickness, $share, 100 - $share, -$offset,
            );
            $offset += $share;
        }

        return $svg.'</svg>';
    }

    /**
     * Short axis labels such as 1.2M or 350K.
     */
    private static function compact(float $value): string
    {
        $absolute = abs($value);

        return match (true) {
            $absolute >= 1_000_000_000 => round($value / 1_000_000_000, 1).'B',
            $absolute >= 1_000_000 => round($value / 1_000_000, 1).'M',
            $absolute >= 1_000 => round($value / 1_000, 1).'K',
            default => (string) round($value),
        };
    }
}
