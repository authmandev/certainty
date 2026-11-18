<script setup lang="ts">
import Reveal from '@/components/landing/Reveal.vue';

const { align = 'center' } = defineProps<{
    eyebrow: string;
    title: string;
    description?: string;
    align?: 'center' | 'start';
}>();
</script>

<template>
    <Reveal
        class="max-w-2xl"
        :class="align === 'center' ? 'mx-auto text-center' : 'text-start'"
    >
        <p
            class="inline-flex items-center gap-2 text-sm font-semibold text-primary"
        >
            <span class="h-px w-6 bg-primary/60"></span>
            {{ eyebrow }}
        </p>
        <h2
            class="mt-3 font-display text-3xl leading-[1.3] font-bold tracking-tight text-balance sm:text-4xl"
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="mt-4 text-lg leading-relaxed text-pretty text-muted-foreground"
        >
            {{ description }}
        </p>
    </Reveal>
</template>
