<script setup lang="ts" generic="T extends { key: string | null }">
import { Plus, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{
    title: string;
    description?: string;
    rows: T[];
    addLabel: string;
    emptyText: string;
    rowLabel: string;
    error?: string;
}>();

defineEmits<{
    add: [];
    remove: [index: number];
}>();
</script>

<template>
    <section class="space-y-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h3 class="font-display font-bold">
                    {{ title }}
                    <span
                        class="ms-1 rounded-full bg-muted px-2 py-0.5 align-middle text-xs font-medium text-muted-foreground tabular-nums"
                        >{{ rows.length }}</span
                    >
                </h3>
                <p
                    v-if="description"
                    class="mt-1 text-sm text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="$emit('add')"
            >
                <Plus class="size-4" />
                {{ addLabel }}
            </Button>
        </div>

        <p
            v-if="error"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950/50 dark:text-red-300"
        >
            {{ error }}
        </p>

        <div
            v-if="rows.length === 0"
            class="rounded-2xl border border-dashed px-6 py-8 text-center text-sm text-muted-foreground"
        >
            {{ emptyText }}
        </div>

        <TransitionGroup
            v-else
            tag="ol"
            class="space-y-3"
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0"
        >
            <li
                v-for="(row, index) in rows"
                :key="row.key ?? index"
                class="rounded-2xl border bg-muted/20 p-4"
            >
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-muted-foreground"
                        >{{ rowLabel }} {{ index + 1 }}</span
                    >
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        class="size-8 text-muted-foreground hover:text-destructive"
                        :aria-label="`حذف ${rowLabel} ${index + 1}`"
                        @click="$emit('remove', index)"
                    >
                        <Trash2 class="size-4" />
                    </Button>
                </div>
                <slot :row="row" :index="index" />
            </li>
        </TransitionGroup>
    </section>
</template>
