<script setup lang="ts">
import { computed, useId, useTemplateRef } from 'vue';
import { useInView } from '@/composables/useInView';
import { formatNumber } from '@/lib/format';

type Series = {
    label: string;
    values: number[];
    color: string;
    fill?: boolean;
};

const {
    series,
    labels,
    height = 220,
} = defineProps<{
    series: Series[];
    labels: string[];
    height?: number;
}>();

const width = 520;
const padding = { top: 16, bottom: 28, start: 16, end: 52 };
const gradientId = useId();

const element = useTemplateRef<SVGElement>('element');
const inView = useInView(element);

const maxValue = computed(
    () => Math.max(...series.flatMap((item) => item.values)) * 1.1,
);

/**
 * The time axis runs right-to-left so the first year sits on the right, matching the Arabic reading direction.
 */
function x(index: number): number {
    const step =
        (width - padding.start - padding.end) / Math.max(labels.length - 1, 1);

    return width - padding.end - index * step;
}

function y(value: number): number {
    const chartHeight = height - padding.top - padding.bottom;

    return padding.top + chartHeight - (value / maxValue.value) * chartHeight;
}

function linePath(values: number[]): string {
    return values
        .map(
            (value, index) =>
                `${index === 0 ? 'M' : 'L'}${x(index)},${y(value)}`,
        )
        .join(' ');
}

function areaPath(values: number[]): string {
    const baseline = height - padding.bottom;

    return `${linePath(values)} L${x(values.length - 1)},${baseline} L${x(0)},${baseline} Z`;
}

const gridLines = computed(() =>
    [0.25, 0.5, 0.75, 1].map((ratio) => ({
        y: y(maxValue.value * ratio),
        label: formatNumber(Math.round((maxValue.value * ratio) / 1000)) + 'k',
    })),
);
</script>

<template>
    <svg
        ref="element"
        :viewBox="`0 0 ${width} ${height}`"
        class="h-auto w-full overflow-visible"
        style="direction: ltr"
        role="img"
        :aria-label="series.map((item) => item.label).join('، ')"
    >
        <defs>
            <linearGradient
                v-for="(item, index) in series"
                :id="`${gradientId}-${index}`"
                :key="item.label"
                x1="0"
                x2="0"
                y1="0"
                y2="1"
            >
                <stop
                    offset="0%"
                    :style="{ stopColor: item.color, stopOpacity: 0.28 }"
                />
                <stop
                    offset="100%"
                    :style="{ stopColor: item.color, stopOpacity: 0 }"
                />
            </linearGradient>
        </defs>

        <g class="text-muted-foreground">
            <g v-for="line in gridLines" :key="line.y">
                <line
                    :x1="padding.start"
                    :x2="width - padding.end"
                    :y1="line.y"
                    :y2="line.y"
                    stroke="currentColor"
                    stroke-opacity="0.15"
                    stroke-dasharray="4 6"
                />
                <text
                    :x="width - padding.end + 10"
                    :y="line.y + 4"
                    fill="currentColor"
                    font-size="11"
                    text-anchor="start"
                >
                    {{ line.label }}
                </text>
            </g>
            <text
                v-for="(label, index) in labels"
                :key="label"
                :x="x(index)"
                :y="height - 6"
                fill="currentColor"
                font-size="11"
                text-anchor="middle"
            >
                {{ label }}
            </text>
        </g>

        <g v-for="(item, index) in series" :key="item.label">
            <path
                v-if="item.fill"
                :d="areaPath(item.values)"
                :fill="`url(#${gradientId}-${index})`"
                class="transition-opacity duration-1000"
                :class="inView ? 'opacity-100' : 'opacity-0'"
                :style="{ transitionDelay: `${400 + index * 200}ms` }"
            />
            <path
                :d="linePath(item.values)"
                fill="none"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                pathLength="1"
                stroke-dasharray="1"
                :stroke-dashoffset="1"
                :class="inView ? 'animate-draw' : ''"
                :style="{
                    stroke: item.color,
                    animationDelay: `${index * 200}ms`,
                }"
            />
            <circle
                v-for="(value, pointIndex) in item.values"
                :key="pointIndex"
                :cx="x(pointIndex)"
                :cy="y(value)"
                r="3.5"
                class="transition-opacity duration-500"
                :class="inView ? 'opacity-100' : 'opacity-0'"
                :style="{
                    fill: item.color,
                    transitionDelay: `${900 + pointIndex * 120}ms`,
                }"
            />
        </g>
    </svg>
</template>
