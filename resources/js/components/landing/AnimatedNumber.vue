<script setup lang="ts">
import {
    TransitionPresets,
    usePreferredReducedMotion,
    useTransition,
} from '@vueuse/core';
import { computed, ref, useTemplateRef, watch } from 'vue';
import { useInView } from '@/composables/useInView';
import { formatNumber } from '@/lib/format';

const {
    value,
    decimals = 0,
    duration = 1600,
} = defineProps<{
    value: number;
    decimals?: number;
    duration?: number;
}>();

const element = useTemplateRef<HTMLElement>('element');
const inView = useInView(element);
const reducedMotion = usePreferredReducedMotion();
const target = ref(0);

const animated = useTransition(target, {
    duration,
    transition: TransitionPresets.easeOutExpo,
});

watch(
    inView,
    (visible) => {
        if (visible) {
            target.value = value;
        }
    },
    { immediate: true },
);

const display = computed(() =>
    formatNumber(
        reducedMotion.value === 'reduce' ? value : animated.value,
        decimals,
    ),
);
</script>

<template>
    <span
        ref="element"
        class="tabular-nums"
        :aria-label="formatNumber(value, decimals)"
        >{{ display }}</span
    >
</template>
