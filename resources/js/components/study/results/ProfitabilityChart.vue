<script setup lang="ts">
import { computed } from 'vue';
import { formatCompact } from '@/lib/format';

export type ChartSeries = {
    label: string;
    values: number[];
    color: string;
    dashed?: boolean;
};

const { series, labels } = defineProps<{
    series: ChartSeries[];
    labels: string[];
}>();

const width = 340;
const height = 190;
const padding = { top: 12, bottom: 26, start: 8, end: 48 };

const range = computed(() => {
    const values = series.flatMap((item) => item.values);
    const max = Math.max(0, ...values);
    const min = Math.min(0, ...values);
    const span = max - min || 1;

    return { max: max + span * 0.08, min: min < 0 ? min - span * 0.08 : 0 };
});

/**
 * Year one sits on the right so the time axis reads in the Arabic direction.
 */
function x(index: number): number {
    const step =
        (width - padding.start - padding.end) / Math.max(labels.length - 1, 1);

    return labels.length === 1
        ? (width - padding.end + padding.start) / 2
        : width - padding.end - index * step;
}

function y(value: number): number {
    const chartHeight = height - padding.top - padding.bottom;
    const { max, min } = range.value;

    return padding.top + ((max - value) / (max - min)) * chartHeight;
}

function linePath(values: number[]): string {
    return values
        .map(
            (value, index) =>
                `${index === 0 ? 'M' : 'L'}${x(index).toFixed(1)},${y(value).toFixed(1)}`,
        )
        .join(' ');
}

const ticks = computed(() => {
    const { max, min } = range.value;

    return [0, 0.25, 0.5, 0.75, 1].map((ratio) => {
        const value = min + (max - min) * ratio;

        return { y: y(value), label: formatCompact(value) };
    });
});

const hovered = defineModel<number | null>('hovered', { default: null });
</script>

<template>
    <svg
        :viewBox="`0 0 ${width} ${height}`"
        class="h-auto w-full overflow-visible"
        style="direction: ltr"
        role="img"
        :aria-label="series.map((item) => item.label).join('، ')"
        @mouseleave="hovered = null"
    >
        <g class="text-muted-foreground">
            <g v-for="tick in ticks" :key="tick.y">
                <line
                    :x1="padding.start"
                    :x2="width - padding.end"
                    :y1="tick.y"
                    :y2="tick.y"
                    stroke="currentColor"
                    stroke-opacity="0.12"
                    stroke-dasharray="3 5"
                />
                <text
                    :x="width - padding.end + 8"
                    :y="tick.y + 3.5"
                    fill="currentColor"
                    font-size="10"
                >
                    {{ tick.label }}
                </text>
            </g>
            <line
                v-if="range.min < 0"
                :x1="padding.start"
                :x2="width - padding.end"
                :y1="y(0)"
                :y2="y(0)"
                stroke="currentColor"
                stroke-opacity="0.45"
            />
            <text
                v-for="(label, index) in labels"
                :key="label"
                :x="x(index)"
                :y="height - 6"
                fill="currentColor"
                font-size="10"
                text-anchor="middle"
                :font-weight="hovered === index ? 700 : 400"
            >
                {{ label }}
            </text>
            <line
                v-if="hovered !== null"
                :x1="x(hovered)"
                :x2="x(hovered)"
                :y1="padding.top"
                :y2="height - padding.bottom"
                stroke="currentColor"
                stroke-opacity="0.3"
            />
        </g>

        <g v-for="item in series" :key="item.label">
            <path
                :d="linePath(item.values)"
                fill="none"
                stroke-width="2.25"
                stroke-linecap="round"
                stroke-linejoin="round"
                :stroke-dasharray="item.dashed ? '5 4' : undefined"
                :style="{ stroke: item.color }"
                class="transition-[d] duration-500 ease-out"
            />
            <circle
                v-for="(value, index) in item.values"
                :key="index"
                :cx="x(index)"
                :cy="y(value)"
                :r="hovered === index ? 4 : 2.75"
                :style="{ fill: item.color }"
                class="transition-[cy,r] duration-500 ease-out"
            />
        </g>

        <rect
            v-for="(label, index) in labels"
            :key="`hit-${label}`"
            :x="x(index) - 14"
            :y="padding.top"
            width="28"
            :height="height - padding.top - padding.bottom"
            fill="transparent"
            @mouseenter="hovered = index"
        />
    </svg>
</template>
