<script setup lang="ts">
import { Search } from '@lucide/vue';
import { Input } from '@/components/ui/input';

defineProps<{
    placeholder: string;
}>();

const model = defineModel<string>({ required: true });
</script>

<template>
    <div class="relative w-full sm:w-72">
        <Search
            class="pointer-events-none absolute inset-s-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="model"
            type="search"
            class="ps-9"
            :placeholder="placeholder"
            aria-label="بحث"
        />
    </div>
</template>
