<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { FileUp, LoaderCircle, X } from '@lucide/vue';
import { computed, ref, useTemplateRef } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { formatFileSize, formatNumber } from '@/lib/format';
import { store } from '@/routes/studies/receipts';
import type { ReceiptRules } from '@/types';

const { studyId, rules, replacing } = defineProps<{
    studyId: number;
    rules: ReceiptRules;
    replacing?: boolean;
}>();

const form = useForm<{ receipt: File | null }>({ receipt: null });
const input = useTemplateRef<HTMLInputElement>('input');
const dragging = ref(false);
const clientError = ref<string | null>(null);

const accept = computed(() =>
    rules.extensions.map((extension) => `.${extension}`).join(','),
);
const maxMegabytes = computed(() => Math.round(rules.max_kilobytes / 1024));

/**
 * Quick checks so the customer hears about a wrong file before uploading it;
 * the server validates again, including the file's contents.
 */
function choose(file: File | undefined): void {
    clientError.value = null;
    form.clearErrors();

    if (!file) {
        return;
    }

    const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

    if (!rules.extensions.includes(extension)) {
        clientError.value = 'يجب أن يكون الإيصال صورة JPG أو PNG أو ملف PDF.';

        return;
    }

    if (file.size > rules.max_kilobytes * 1024) {
        clientError.value = `يجب ألا يتجاوز حجم الإيصال ${formatNumber(maxMegabytes.value)} ميجابايت.`;

        return;
    }

    form.receipt = file;
}

function onDrop(event: DragEvent): void {
    dragging.value = false;
    choose(event.dataTransfer?.files[0]);
}

function clear(): void {
    form.reset();
    clientError.value = null;

    if (input.value) {
        input.value.value = '';
    }
}

function submit(): void {
    form.post(store(studyId).url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: clear,
    });
}
</script>

<template>
    <form class="space-y-3" @submit.prevent="submit">
        <label
            v-if="!form.receipt"
            for="receipt"
            class="flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed p-6 text-center transition-colors hover:border-primary/60 hover:bg-primary/5"
            :class="dragging ? 'border-primary bg-primary/5' : 'border-border'"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <span
                class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary"
            >
                <FileUp class="size-5" />
            </span>
            <span class="text-sm font-medium">
                {{ replacing ? 'ارفع إيصالًا جديدًا' : 'ارفع إيصال التحويل' }}
            </span>
            <span class="text-xs text-muted-foreground">
                اسحب الملف إلى هنا أو اضغط للاختيار · JPG أو PNG أو PDF حتى
                {{ formatNumber(maxMegabytes) }} ميجابايت
            </span>
            <input
                id="receipt"
                ref="input"
                type="file"
                class="sr-only"
                :accept="accept"
                @change="choose(($event.target as HTMLInputElement).files?.[0])"
            />
        </label>

        <div
            v-else
            class="flex items-center justify-between gap-3 rounded-xl border bg-muted/40 p-3 text-sm"
        >
            <div class="min-w-0">
                <p class="truncate font-medium" dir="auto">
                    {{ form.receipt.name }}
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ formatFileSize(form.receipt.size) }}
                </p>
            </div>
            <button
                type="button"
                class="flex size-8 shrink-0 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground"
                aria-label="إزالة الملف"
                :disabled="form.processing"
                @click="clear"
            >
                <X class="size-4" />
            </button>
        </div>

        <div
            v-if="form.progress"
            class="h-1.5 overflow-hidden rounded-full bg-muted"
        >
            <div
                class="h-full rounded-full bg-primary transition-[width]"
                :style="{ width: `${form.progress.percentage}%` }"
            ></div>
        </div>

        <InputError :message="clientError ?? form.errors.receipt" />

        <Button
            type="submit"
            class="w-full"
            :disabled="!form.receipt || form.processing"
        >
            <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
            إرسال الإيصال للمراجعة
        </Button>
    </form>
</template>
