<script setup lang="ts">
import { Check } from '@lucide/vue';
import type { SectionKey, SectionProgress } from '@/types';

defineProps<{
    sections: SectionProgress[];
    current: SectionKey;
}>();

defineEmits<{
    select: [key: SectionKey];
}>();
</script>

<template>
    <nav aria-label="مراحل الدراسة">
        <ol
            class="flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:gap-1 lg:overflow-visible lg:pb-0"
        >
            <li
                v-for="(section, index) in sections"
                :key="section.key"
                class="shrink-0"
            >
                <button
                    type="button"
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start text-sm transition-colors"
                    :class="
                        current === section.key
                            ? 'bg-primary/10 font-semibold text-primary'
                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                    "
                    :aria-current="current === section.key ? 'step' : undefined"
                    @click="$emit('select', section.key)"
                >
                    <span
                        class="flex size-7 shrink-0 items-center justify-center rounded-full border text-xs font-bold transition-colors"
                        :class="
                            section.is_complete
                                ? 'border-primary bg-primary text-primary-foreground'
                                : current === section.key
                                  ? 'border-primary text-primary'
                                  : ''
                        "
                    >
                        <Check v-if="section.is_complete" class="size-3.5" />
                        <template v-else>{{ index + 1 }}</template>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate">{{ section.title }}</span>
                        <span
                            class="hidden text-xs font-normal text-muted-foreground tabular-nums lg:block"
                        >
                            {{ section.completed }} من {{ section.total }}
                        </span>
                    </span>
                </button>
            </li>
        </ol>
    </nav>
</template>
