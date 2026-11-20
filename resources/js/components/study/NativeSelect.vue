<script setup lang="ts" generic="T extends string | number">
import { ChevronDown } from '@lucide/vue';
import type { OptionItem } from '@/types';

const model = defineModel<T | null>({ required: true });

defineProps<{
    id: string;
    options: OptionItem<T>[];
    placeholder?: string;
    invalid?: boolean;
}>();

function onChange(event: Event, options: OptionItem<T>[]): void {
    const index = (event.target as HTMLSelectElement).selectedIndex - 1;
    model.value = index >= 0 ? options[index].value : null;
}
</script>

<template>
    <div class="relative">
        <select
            :id="id"
            class="h-10 w-full appearance-none rounded-lg border border-input bg-background ps-3 pe-9 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive dark:bg-input/30"
            :class="model === null ? 'text-muted-foreground' : ''"
            :aria-invalid="invalid || undefined"
            @change="onChange($event, options)"
        >
            <option
                value=""
                :selected="model === null"
                class="bg-popover text-muted-foreground"
            >
                {{ placeholder ?? 'اختر…' }}
            </option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
                :selected="option.value === model"
                class="bg-popover text-popover-foreground"
            >
                {{ option.label }}
            </option>
        </select>
        <ChevronDown
            class="pointer-events-none absolute inset-y-0 inset-e-3 my-auto size-4 text-muted-foreground"
            aria-hidden="true"
        />
    </div>
</template>
