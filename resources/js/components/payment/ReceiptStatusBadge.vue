<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { LabelledStatus, ReceiptStatus } from '@/types';

defineProps<{
    status: LabelledStatus<ReceiptStatus>;
}>();

const classes: Record<ReceiptStatus, string> = {
    submitted:
        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
    approved:
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
    rejected:
        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
    replaced:
        'border-neutral-200 bg-neutral-50 text-neutral-600 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400',
};
</script>

<template>
    <Badge variant="outline" :class="classes[status.value]">{{
        status.label
    }}</Badge>
</template>
