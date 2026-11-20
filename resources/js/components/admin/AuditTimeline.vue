<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Banknote,
    CalendarPlus,
    CircleCheck,
    FileText,
    History,
    RotateCcw,
    XCircle,
} from '@lucide/vue';
import type { Component } from 'vue';
import { formatDateTime } from '@/lib/format';
import { show as studyRoute } from '@/routes/admin/studies';
import type { AuditActionValue, AuditLogEntry } from '@/types';

defineProps<{
    entries: AuditLogEntry[];
    showStudy?: boolean;
    compact?: boolean;
}>();

const icons: Record<AuditActionValue, Component> = {
    receipt_submitted: Banknote,
    payment_approved: CircleCheck,
    payment_rejected: XCircle,
    draft_extended: CalendarPlus,
    study_finalized: FileText,
    study_reopened: RotateCcw,
    report_generated: FileText,
};

const tones: Record<AuditActionValue, string> = {
    receipt_submitted: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
    payment_approved:
        'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    payment_rejected: 'bg-red-500/10 text-red-600 dark:text-red-400',
    draft_extended: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
    study_finalized: 'bg-primary/10 text-primary',
    study_reopened: 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
    report_generated: 'bg-muted text-muted-foreground',
};

/**
 * A short Arabic summary of what changed, built from the stored values.
 */
function details(entry: AuditLogEntry): string[] {
    const lines: string[] = [];
    const next = entry.new_values ?? {};
    const previous = entry.old_values ?? {};

    if (typeof next.extension_days === 'number') {
        lines.push(`أُضيف ${next.extension_days} يومًا`);
    }

    if (typeof next.editing_days === 'number') {
        lines.push(`مدة التعديل ${next.editing_days} يومًا`);
    }

    if (
        typeof previous.expires_at === 'string' &&
        typeof next.expires_at === 'string'
    ) {
        lines.push(
            `الموعد: ${formatDateTime(previous.expires_at)} ← ${formatDateTime(next.expires_at)}`,
        );
    }

    if (typeof next.report_version === 'number') {
        lines.push(`إصدار التقرير ${next.report_version}`);
    } else if (typeof previous.report_version === 'number') {
        lines.push(`الإصدار السابق ${previous.report_version}`);
    }

    return lines;
}
</script>

<template>
    <ol
        v-if="entries.length > 0"
        :class="compact ? 'space-y-3.5' : 'space-y-4'"
    >
        <li
            v-for="(entry, index) in entries"
            :key="entry.id"
            class="relative flex gap-3"
        >
            <span
                v-if="compact && index < entries.length - 1"
                class="absolute start-4 top-9 -bottom-3.5 w-px bg-gradient-to-b from-border to-transparent"
                aria-hidden="true"
            ></span>
            <span
                class="relative flex size-8 shrink-0 items-center justify-center rounded-full ring-4 ring-card"
                :class="tones[entry.action.value]"
            >
                <component :is="icons[entry.action.value]" class="size-4" />
            </span>
            <div class="min-w-0 flex-1 text-sm">
                <div
                    class="flex flex-wrap items-baseline justify-between gap-x-3"
                >
                    <p class="font-medium">
                        {{ entry.action.label }}
                        <Link
                            v-if="showStudy && entry.study"
                            :href="studyRoute(entry.study.id)"
                            class="ms-1 font-normal text-primary hover:underline"
                            >{{ entry.study.reference }}</Link
                        >
                    </p>
                    <time
                        class="text-xs text-muted-foreground"
                        :datetime="entry.created_at"
                        >{{ formatDateTime(entry.created_at) }}</time
                    >
                </div>
                <p class="text-xs text-muted-foreground">
                    {{
                        entry.actor
                            ? `${entry.actor.name}${entry.actor.is_admin ? ' (مدير)' : ''}`
                            : 'النظام'
                    }}
                </p>
                <p
                    v-for="line in compact ? [] : details(entry)"
                    :key="line"
                    class="mt-0.5 text-xs text-muted-foreground"
                >
                    {{ line }}
                </p>
                <p
                    v-if="entry.reason"
                    class="mt-1.5 w-fit max-w-full rounded-lg bg-muted/60 px-2.5 py-1.5 text-xs leading-relaxed"
                    :class="compact && 'line-clamp-1'"
                >
                    السبب: {{ entry.reason }}
                </p>
            </div>
        </li>
    </ol>
    <p v-else class="flex items-center gap-2 text-sm text-muted-foreground">
        <History class="size-4" />
        لا توجد عمليات مسجلة بعد.
    </p>
</template>
