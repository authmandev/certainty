<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    id: string;
    label: string;
    hint?: string;
    error?: string;
}>();
</script>

<template>
    <div class="grid content-start gap-2">
        <Label :for="id" class="text-sm font-medium">{{ label }}</Label>
        <slot />
        <p
            v-if="hint && !error"
            class="text-xs leading-relaxed text-muted-foreground"
        >
            {{ hint }}
        </p>
        <InputError :message="error" />
    </div>
</template>
