<script setup lang="ts">
import { computed, useTemplateRef } from 'vue';
import { useInView } from '@/composables/useInView';

type Group = {
    label: string;
    values: number[];
};

const {
    groups,
    colors,
    height = 180,
} = defineProps<{
    groups: Group[];
    colors: string[];
    height?: number;
}>();

const width = 320;
const baseline = computed(() => height - 22);
const element = useTemplateRef<SVGElement>('element');
const inView = useInView(element);

const maxValue = computed(
    () => Math.max(...groups.flatMap((group) => group.values)) * 1.08,
);

const groupWidth = computed(() => width / groups.length);
const barWidth = computed(
    () => (groupWidth.value * 0.62) / Math.max(colors.length, 1),
);

/**
 * Groups are laid out from the right so the first year reads first in RTL.
 */
function barX(groupIndex: number, barIndex: number): number {
    const groupStart = width - (groupIndex + 1) * groupWidth.value;
    const inset = groupWidth.value * 0.19;

    return groupStart + inset + barIndex * barWidth.value;
}

function barHeight(value: number): number {
    return (value / maxValue.value) * (baseline.value - 8);
}
</script>

<template>
    <svg
        ref="element"
        :viewBox="`0 0 ${width} ${height}`"
        class="h-auto w-full"
        style="direction: ltr"
        role="img"
        aria-hidden="true"
    >
        <line
            x1="0"
            :x2="width"
            :y1="baseline"
            :y2="baseline"
            stroke="currentColor"
            stroke-opacity="0.15"
        />
        <g v-for="(group, groupIndex) in groups" :key="group.label">
            <rect
                v-for="(value, barIndex) in group.values"
                :key="barIndex"
                :x="barX(groupIndex, barIndex)"
                :y="baseline - barHeight(value)"
                :width="barWidth - 3"
                :height="barHeight(value)"
                rx="3"
                :class="inView ? 'animate-grow-up' : 'scale-y-0'"
                :style="{
                    fill: colors[barIndex],
                    transformBox: 'fill-box',
                    transformOrigin: 'bottom',
                    animationDelay: `${groupIndex * 90 + barIndex * 60}ms`,
                }"
            />
            <text
                :x="width - (groupIndex + 0.5) * groupWidth"
                :y="height - 6"
                text-anchor="middle"
                font-size="11"
                fill="currentColor"
                fill-opacity="0.6"
            >
                {{ group.label }}
            </text>
        </g>
    </svg>
</template>
