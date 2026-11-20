<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';

const model = defineModel<number | null>({ required: true });

const {
    suffix,
    step = 'any',
    min = 0,
} = defineProps<{
    id: string;
    suffix?: string;
    step?: string;
    min?: number;
    placeholder?: string;
    invalid?: boolean;
}>();

/**
 * Bridges the native input's empty string to null so cleared fields save as empty.
 */
const value = computed({
    get: () => model.value ?? '',
    set: (next: string | number) => {
        model.value = next === '' || next === null ? null : Number(next);
    },
});
</script>

<template>
    <div class="relative">
        <Input
            :id="id"
            v-model="value"
            type="number"
            inputmode="decimal"
            :step="step"
            :min="min"
            :placeholder="placeholder"
            :aria-invalid="invalid || undefined"
            class="h-10 tabular-nums"
            :class="suffix ? 'pe-14' : ''"
        />
        <span
            v-if="suffix"
            class="pointer-events-none absolute inset-y-0 inset-e-3 my-auto flex items-center text-xs text-muted-foreground"
            >{{ suffix }}</span
        >
    </div>
</template>
