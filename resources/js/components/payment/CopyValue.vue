<script setup lang="ts">
import { Check, Copy } from '@lucide/vue';
import { useClipboard } from '@vueuse/core';

const { label, value } = defineProps<{
    label: string;
    value: string;
}>();

const { copy, copied, isSupported } = useClipboard({ copiedDuring: 1800 });
</script>

<template>
    <div class="flex items-center justify-between gap-3 py-2.5">
        <div class="min-w-0">
            <p class="text-xs text-muted-foreground">{{ label }}</p>
            <p class="mt-0.5 font-medium break-all" dir="auto">{{ value }}</p>
        </div>
        <button
            v-if="isSupported"
            type="button"
            class="flex size-8 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            :aria-label="copied ? 'تم النسخ' : `نسخ ${label}`"
            @click="copy(value)"
        >
            <Check v-if="copied" class="size-4 text-emerald-600" />
            <Copy v-else class="size-4" />
        </button>
    </div>
</template>
