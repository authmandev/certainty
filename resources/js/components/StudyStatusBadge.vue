<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { LabelledStatus, StudyStatus } from '@/types';

const props = defineProps<{
    status: LabelledStatus<StudyStatus>;
}>();

const classes: Record<StudyStatus, string> = {
    draft: 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-300',
    expired:
        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
    finalized:
        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300',
};

const badgeClass = computed(() => classes[props.status.value]);
</script>

<template>
    <Badge variant="outline" :class="badgeClass">{{ status.label }}</Badge>
</template>
