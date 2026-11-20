<script setup lang="ts">
import { Download, Eye, FileText } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/format';
import type { StudyReportVersion } from '@/types';

defineProps<{
    reports: StudyReportVersion[];
}>();
</script>

<template>
    <section class="rounded-2xl border bg-card p-6 text-sm">
        <div class="flex items-center gap-2.5">
            <span
                class="flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
            >
                <FileText class="size-4" />
            </span>
            <h2 class="font-semibold">التقرير النهائي</h2>
        </div>

        <ul class="mt-4 space-y-3">
            <li
                v-for="(report, index) in reports"
                :key="report.id"
                class="rounded-xl border p-3"
                data-test="report-version"
            >
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-medium">
                        الإصدار {{ report.version }}
                        <span
                            v-if="index === 0"
                            class="ms-1 text-xs font-normal text-emerald-600 dark:text-emerald-400"
                            >(الحالي)</span
                        >
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ formatDateTime(report.finalized_at) }}
                    </p>
                </div>
                <p
                    v-if="!report.is_generated"
                    class="mt-1 text-xs text-muted-foreground"
                >
                    يُجهَّز ملف PDF الآن، ويمكنك فتحه مباشرة وسيُنشأ عند الطلب.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Button size="sm" as-child>
                        <a :href="report.download_url">
                            <Download class="size-4" />
                            تنزيل PDF
                        </a>
                    </Button>
                    <Button size="sm" variant="outline" as-child>
                        <a
                            :href="report.view_url"
                            target="_blank"
                            rel="noopener"
                        >
                            <Eye class="size-4" />
                            عرض وطباعة
                        </a>
                    </Button>
                </div>
            </li>
        </ul>
    </section>
</template>
