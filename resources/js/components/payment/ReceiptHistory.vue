<script setup lang="ts">
import { ExternalLink, FileText, ImageIcon } from '@lucide/vue';
import ReceiptStatusBadge from '@/components/payment/ReceiptStatusBadge.vue';
import { formatDateTime, formatFileSize } from '@/lib/format';
import type { PaymentReceipt } from '@/types';

defineProps<{
    receipts: PaymentReceipt[];
    showReviewer?: boolean;
}>();
</script>

<template>
    <ol class="space-y-3">
        <li
            v-for="receipt in receipts"
            :key="receipt.id"
            class="rounded-xl border p-3 text-sm"
            data-test="receipt-history-item"
        >
            <div class="flex flex-wrap items-start justify-between gap-2">
                <a
                    :href="receipt.url"
                    target="_blank"
                    rel="noopener"
                    class="flex min-w-0 items-center gap-2 font-medium hover:underline"
                >
                    <ImageIcon
                        v-if="receipt.mime_type.startsWith('image/')"
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                    <FileText
                        v-else
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                    <span class="truncate" dir="auto">{{
                        receipt.file_name
                    }}</span>
                    <ExternalLink
                        class="size-3.5 shrink-0 text-muted-foreground"
                    />
                </a>
                <ReceiptStatusBadge :status="receipt.status" />
            </div>
            <p class="mt-1.5 text-xs text-muted-foreground">
                رُفع في {{ formatDateTime(receipt.submitted_at) }} ·
                {{ formatFileSize(receipt.file_size) }}
            </p>
            <p
                v-if="receipt.reviewed_at"
                class="mt-1 text-xs text-muted-foreground"
            >
                رُوجع في {{ formatDateTime(receipt.reviewed_at) }}
                <template v-if="showReviewer && receipt.reviewer">
                    بواسطة {{ receipt.reviewer.name }}
                </template>
            </p>
            <p
                v-if="receipt.rejection_reason"
                class="mt-2 rounded-lg bg-red-50 p-2 text-xs leading-relaxed text-red-700 dark:bg-red-950 dark:text-red-300"
            >
                سبب الرفض: {{ receipt.rejection_reason }}
            </p>
        </li>
    </ol>
</template>
