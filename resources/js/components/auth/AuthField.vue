<script setup lang="ts">
import type { Component } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

defineProps<{
    id: string;
    label: string;
    icon: Component;
    error?: string;
}>();
</script>

<template>
    <div class="grid gap-2">
        <div class="flex items-center justify-between gap-3">
            <Label :for="id" class="text-sm font-medium">{{ label }}</Label>
            <slot name="aside" />
        </div>
        <div class="group/field relative">
            <component
                :is="icon"
                class="pointer-events-none absolute inset-y-0 inset-s-4 z-10 my-auto size-4 text-muted-foreground transition-colors group-focus-within/field:text-primary"
                aria-hidden="true"
            />
            <slot />
        </div>
        <InputError :message="error" />
    </div>
</template>
