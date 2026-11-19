<script setup lang="ts">
import { CircleAlert, CloudCheck, LoaderCircle, PencilLine } from '@lucide/vue';
import type { SaveStatus } from '@/composables/useAutosave';

defineProps<{
    status: SaveStatus;
    lastSavedAt: Date | null;
}>();

defineEmits<{
    retry: [];
}>();

function formatTime(date: Date): string {
    return new Intl.DateTimeFormat('ar-SA-u-nu-latn', {
        timeStyle: 'short',
    }).format(date);
}
</script>

<template>
    <div
        class="flex items-center gap-1.5 text-sm"
        role="status"
        aria-live="polite"
    >
        <template v-if="status === 'saving'">
            <LoaderCircle class="size-4 animate-spin text-muted-foreground" />
            <span class="text-muted-foreground">جارٍ الحفظ…</span>
        </template>
        <template v-else-if="status === 'error'">
            <CircleAlert class="size-4 text-red-600 dark:text-red-400" />
            <span class="text-red-600 dark:text-red-400">لم يُحفظ</span>
            <span class="text-muted-foreground" aria-hidden="true">·</span>
            <button
                type="button"
                class="font-medium text-red-600 underline underline-offset-4 hover:no-underline dark:text-red-400"
                @click="$emit('retry')"
            >
                إعادة المحاولة
            </button>
        </template>
        <template v-else-if="status === 'dirty'">
            <PencilLine class="size-4 text-amber-600 dark:text-amber-400" />
            <span class="text-muted-foreground">تغييرات غير محفوظة</span>
        </template>
        <template v-else>
            <CloudCheck class="size-4 text-emerald-600 dark:text-emerald-400" />
            <span class="text-muted-foreground">
                {{
                    lastSavedAt
                        ? `تم الحفظ ${formatTime(lastSavedAt)}`
                        : 'كل التغييرات محفوظة'
                }}
            </span>
        </template>
    </div>
</template>
