<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { LabelledStatus, PaymentStatus } from '@/types';

const props = defineProps<{
    status: LabelledStatus<PaymentStatus>;
}>();

const classes: Record<PaymentStatus, string> = {
    pending:
        'border-neutral-200 bg-neutral-50 text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300',
    under_review:
        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
    approved:
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
    rejected:
        'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
};

const badgeClass = computed(() => classes[props.status.value]);
</script>

<template>
    <Badge variant="outline" :class="badgeClass">{{ status.label }}</Badge>
</template>
