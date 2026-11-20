<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <section class="rounded-2xl border bg-card p-5 sm:p-6">
        <header v-if="title" class="mb-5">
            <h3 class="font-display text-lg font-bold">{{ title }}</h3>
            <p
                v-if="description"
                class="mt-1 text-sm leading-relaxed text-muted-foreground"
            >
                {{ description }}
            </p>
        </header>
        <slot />
    </section>
</template>
