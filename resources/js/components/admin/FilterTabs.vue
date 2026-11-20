<script setup lang="ts" generic="T extends string">
import type { OptionItem } from '@/types';

defineProps<{
    options: OptionItem<T>[];
    label: string;
    allLabel?: string;
}>();

const model = defineModel<T | null>({ required: true });
</script>

<template>
    <div
        class="flex flex-wrap gap-1 rounded-xl bg-muted p-1 text-sm"
        role="tablist"
        :aria-label="label"
    >
        <button
            type="button"
            role="tab"
            class="rounded-lg px-3 py-1.5 transition-colors"
            :class="
                model === null
                    ? 'bg-background font-medium shadow-sm'
                    : 'text-muted-foreground hover:text-foreground'
            "
            :aria-selected="model === null"
            @click="model = null"
        >
            {{ allLabel ?? 'الكل' }}
        </button>
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            role="tab"
            class="rounded-lg px-3 py-1.5 transition-colors"
            :class="
                model === option.value
                    ? 'bg-background font-medium shadow-sm'
                    : 'text-muted-foreground hover:text-foreground'
            "
            :aria-selected="model === option.value"
            @click="model = option.value"
        >
            {{ option.label }}
        </button>
    </div>
</template>
