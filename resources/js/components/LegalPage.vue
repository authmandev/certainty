<script setup lang="ts">
defineProps<{
    title: string;
    updatedAt: string;
    sections: { heading: string; paragraphs: string[] }[];
}>();
</script>

<template>
    <article class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
        <h1 class="text-3xl font-bold tracking-tight">{{ title }}</h1>
        <p class="mt-2 text-sm text-muted-foreground">
            آخر تحديث: {{ updatedAt }}
        </p>
        <section
            v-for="section in sections"
            :key="section.heading"
            class="mt-10 space-y-3"
        >
            <h2 class="text-xl font-semibold">{{ section.heading }}</h2>
            <p
                v-for="paragraph in section.paragraphs"
                :key="paragraph"
                class="leading-relaxed text-muted-foreground"
            >
                {{ paragraph }}
            </p>
        </section>
    </article>
</template>
