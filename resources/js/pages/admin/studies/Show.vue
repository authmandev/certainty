<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CalendarPlus, Download, ExternalLink, RotateCcw } from '@lucide/vue';
import { computed, ref } from 'vue';
import AuditTimeline from '@/components/admin/AuditTimeline.vue';
import StudyActionDialog from '@/components/admin/StudyActionDialog.vue';
import StudyInputSummary from '@/components/admin/StudyInputSummary.vue';
import Heading from '@/components/Heading.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import ResultsPanel from '@/components/study/results/ResultsPanel.vue';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { show as showPayment } from '@/routes/admin/payments';
import { extend, index, reopen } from '@/routes/admin/studies';
import { show as showUser } from '@/routes/admin/users';
import type {
    AuditLogEntry,
    Study,
    StudyExtensionEntry,
    StudyInput,
    StudyPaymentDetail,
    StudyPreview,
    StudyProgress,
    StudyReportVersion,
} from '@/types';

const props = defineProps<{
    study: { data: Study };
    customer: { id: number; name: string; email: string };
    timestamps: {
        created_at: string;
        expires_at: string;
        finalized_at: string | null;
    };
    input: StudyInput;
    labels: Record<string, Record<string, string>>;
    progress: StudyProgress;
    preview: StudyPreview | null;
    payment: { data: StudyPaymentDetail } | null;
    reports: { data: StudyReportVersion[] };
    extensions: StudyExtensionEntry[];
    auditLogs: { data: AuditLogEntry[] };
    canExtend: boolean;
    canReopen: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'نظرة عامة', href: dashboard() },
            { title: 'الدراسات', href: index() },
        ],
    },
});

const study = computed(() => props.study.data);
const extendOpen = ref(false);
const reopenOpen = ref(false);

/**
 * Mirrors ExtendStudyAction: an expired draft counts from now, otherwise from its deadline.
 */
const extensionBase = computed(() => {
    const deadline = new Date(props.timestamps.expires_at);

    return deadline > new Date() ? deadline : new Date();
});
</script>

<template>
    <Head :title="study.title" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <Heading
                    :title="study.title"
                    :description="`${study.reference} · ${study.project_name}`"
                />
                <div class="-mt-4 flex flex-wrap gap-2">
                    <StudyStatusBadge :status="study.status" />
                    <PaymentStatusBadge
                        v-if="study.payment"
                        :status="study.payment.status"
                    />
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    v-if="canExtend"
                    variant="outline"
                    @click="extendOpen = true"
                >
                    <CalendarPlus class="size-4" />
                    تمديد المدة
                </Button>
                <Button
                    v-if="canReopen"
                    variant="outline"
                    @click="reopenOpen = true"
                >
                    <RotateCcw class="size-4" />
                    إعادة فتح الدراسة
                </Button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="min-w-0 space-y-6">
                <!-- Overview -->
                <section class="rounded-2xl border bg-card p-5 text-sm">
                    <dl
                        class="grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                العميل
                            </dt>
                            <dd>
                                <Link
                                    :href="showUser(customer.id)"
                                    class="font-medium text-primary hover:underline"
                                    >{{ customer.name }}</Link
                                >
                                <span
                                    class="block text-xs text-muted-foreground"
                                    >{{ customer.email }}</span
                                >
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                نسبة الإكمال
                            </dt>
                            <dd class="font-medium tabular-nums">
                                {{ progress.percentage }}%
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                تاريخ الإنشاء
                            </dt>
                            <dd class="font-medium">
                                {{ formatDateTime(timestamps.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                الموعد النهائي
                            </dt>
                            <dd class="font-medium" data-test="expires-at">
                                {{ formatDateTime(timestamps.expires_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-muted-foreground">
                                تاريخ الإنهاء
                            </dt>
                            <dd class="font-medium">
                                {{
                                    timestamps.finalized_at
                                        ? formatDateTime(
                                              timestamps.finalized_at,
                                          )
                                        : '—'
                                }}
                            </dd>
                        </div>
                        <div v-if="payment">
                            <dt class="text-xs text-muted-foreground">الدفع</dt>
                            <dd>
                                <Link
                                    :href="showPayment(payment.data.id)"
                                    class="flex items-center gap-1 font-medium text-primary hover:underline"
                                >
                                    {{ payment.data.reference_number }}
                                    <ExternalLink class="size-3.5" />
                                </Link>
                            </dd>
                        </div>
                    </dl>
                </section>

                <!-- Input (read-only) -->
                <div>
                    <h2 class="mb-3 font-semibold">
                        بيانات الدراسة (للاطلاع فقط)
                    </h2>
                    <StudyInputSummary :input="input" :labels="labels" />
                </div>

                <!-- Reports -->
                <section class="rounded-2xl border bg-card p-5 text-sm">
                    <h2 class="mb-3 font-semibold">إصدارات التقرير</h2>
                    <ul v-if="reports.data.length > 0" class="divide-y">
                        <li
                            v-for="report in reports.data"
                            :key="report.id"
                            class="flex flex-wrap items-center justify-between gap-2 py-2.5"
                        >
                            <div>
                                <p class="font-medium">
                                    الإصدار {{ report.version }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDateTime(report.finalized_at) }}
                                </p>
                            </div>
                            <Button size="sm" variant="outline" as-child>
                                <a
                                    :href="report.view_url"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    <Download class="size-4" />
                                    فتح PDF
                                </a>
                            </Button>
                        </li>
                    </ul>
                    <p v-else class="text-muted-foreground">
                        لم تُنهَ الدراسة بعد.
                    </p>
                </section>

                <!-- Extensions -->
                <section
                    v-if="extensions.length > 0"
                    class="rounded-2xl border bg-card p-5 text-sm"
                >
                    <h2 class="mb-3 font-semibold">التمديدات</h2>
                    <ul class="divide-y">
                        <li
                            v-for="extension in extensions"
                            :key="extension.id"
                            class="py-2.5"
                        >
                            <p class="font-medium">
                                +{{ extension.extension_days }} يومًا · حتى
                                {{ formatDateTime(extension.new_expires_at) }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ extension.approver }} ·
                                {{ formatDateTime(extension.created_at) }}
                            </p>
                            <p class="mt-1 text-xs">{{ extension.reason }}</p>
                        </li>
                    </ul>
                </section>
            </div>

            <aside class="space-y-6">
                <ResultsPanel
                    :preview="preview"
                    :stale="false"
                    :updating="false"
                />
                <section class="rounded-2xl border bg-card p-5">
                    <h2 class="mb-4 font-semibold">سجل العمليات</h2>
                    <AuditTimeline :entries="auditLogs.data" />
                </section>
            </aside>
        </div>

        <StudyActionDialog
            v-if="canExtend"
            v-model:open="extendOpen"
            :url="extend(study.id).url"
            title="تمديد مدة المسودة"
            :description="
                study.status.value === 'expired'
                    ? 'انتهت مدة الدراسة، وستُحسب الأيام من اليوم. ستعود الدراسة قابلة للتعديل.'
                    : 'ستُضاف الأيام إلى الموعد النهائي الحالي.'
            "
            days-label="عدد أيام التمديد"
            submit-label="تمديد"
            :default-days="14"
            :base-date="extensionBase"
        />
        <StudyActionDialog
            v-if="canReopen"
            v-model:open="reopenOpen"
            :url="reopen(study.id).url"
            title="إعادة فتح الدراسة"
            description="ستعود الدراسة مسودة يمكن للعميل تعديلها، وتبقى إصدارات التقرير السابقة محفوظة. يلزم إنهاؤها من جديد لإصدار تقرير جديد."
            days-label="مدة التعديل المتاحة (أيام)"
            submit-label="إعادة الفتح"
            :default-days="14"
            :base-date="new Date()"
        />
    </div>
</template>
