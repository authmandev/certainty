<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { useInView } from '@/composables/useInView';

const { as = 'div', delay = 0 } = defineProps<{
    as?: string;
    delay?: number;
}>();

const element = useTemplateRef<HTMLElement>('element');
const inView = useInView(element);
</script>

<template>
    <component
        :is="as"
        ref="element"
        :data-reveal="inView ? 'shown' : 'hidden'"
        :style="{ '--reveal-delay': `${delay}ms` }"
    >
        <slot />
    </component>
</template>
