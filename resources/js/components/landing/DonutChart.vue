<script setup lang="ts">
import { computed, useTemplateRef } from 'vue';
import { useInView } from '@/composables/useInView';

type Segment = {
    label: string;
    value: number;
    color: string;
};

const { segments, thickness = 14 } = defineProps<{
    segments: Segment[];
    thickness?: number;
}>();

const element = useTemplateRef<SVGElement>('element');
const inView = useInView(element);
const radius = 50 - thickness / 2;

const arcs = computed(() => {
    const total = segments.reduce((sum, segment) => sum + segment.value, 0);
    let offset = 0;

    return segments.map((segment) => {
        const percent = (segment.value / total) * 100;
        const arc = { ...segment, percent, offset };
        offset += percent;

        return arc;
    });
});
</script>

<template>
    <svg
        ref="element"
        viewBox="0 0 100 100"
        class="size-full -rotate-90"
        role="img"
        :aria-label="segments.map((segment) => segment.label).join('، ')"
    >
        <circle
            cx="50"
            cy="50"
            :r="radius"
            fill="none"
            stroke="currentColor"
            stroke-opacity="0.08"
            :stroke-width="thickness"
        />
        <circle
            v-for="(arc, index) in arcs"
            :key="arc.label"
            cx="50"
            cy="50"
            :r="radius"
            fill="none"
            :stroke-width="thickness"
            pathLength="100"
            :stroke-dasharray="
                inView
                    ? `${arc.percent - 0.8} ${100 - arc.percent + 0.8}`
                    : '0 100'
            "
            :stroke-dashoffset="-arc.offset"
            class="transition-[stroke-dasharray] duration-1000 ease-out"
            :style="{ stroke: arc.color, transitionDelay: `${index * 250}ms` }"
        />
    </svg>
</template>
