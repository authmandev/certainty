<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CalendarClock, Info, PencilLine } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import PaymentPanel from '@/components/payment/PaymentPanel.vue';
import FinalizeStudyCard from '@/components/report/FinalizeStudyCard.vue';
import ReportCard from '@/components/report/ReportCard.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import { edit } from '@/routes/studies';
import type {
    BankDetails,
    Finalization,
    ReceiptRules,
    Study,
    StudyPaymentDetail,
    StudyReportVersion,
} from '@/types';

const props = defineProps<{
    study: { data: Study };
    payment: { data: StudyPaymentDetail } | null;
    bank: BankDetails;
    receiptRules: ReceiptRules;
    finalization: Finalization | null;
    reports: { data: StudyReportVersion[] };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'دراساتي', href: dashboard() }],
    },
});

const study = computed(() => props.study.data);
</script>

<template>
    <Head :title="study.title" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading :title="study.title" :description="study.project_name" />
            <div class="flex flex-wrap gap-2">
                <StudyStatusBadge :status="study.status" />
                <PaymentStatusBadge
                    v-if="study.payment"
                    :status="study.payment.status"
                />
            </div>
        </div>

        <div
            v-if="study.status.value === 'expired'"
            class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200"
        >
            <Info class="mt-0.5 size-4 shrink-0" />
            <p>
                انتهت مدة إكمال هذه الدراسة ولم يعد بالإمكان تعديلها. بياناتك
                محفوظة، ويمكنك التواصل معنا لطلب تمديد المدة.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border bg-card p-6">
                    <h2 class="mb-4 font-semibold">بيانات الدراسة</h2>
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-muted-foreground">رقم الدراسة</dt>
                            <dd class="font-medium" dir="ltr">
                                {{ study.reference }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">تاريخ الإنشاء</dt>
                            <dd class="font-medium">
                                {{ formatDate(study.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">
                                آخر موعد للإكمال
                            </dt>
                            <dd class="font-medium">
                                {{ formatDate(study.expires_at) }}
                            </dd>
                        </div>
                        <div v-if="study.days_remaining !== null">
                            <dt class="text-muted-foreground">
                                المدة المتبقية
                            </dt>
                            <dd class="flex items-center gap-1.5 font-medium">
                                <CalendarClock class="size-4" />
                                {{ study.days_remaining }} يومًا
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-6 rounded-xl bg-muted/60 p-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground"
                                >نسبة إكمال البيانات</span
                            >
                            <span class="font-display font-bold tabular-nums"
                                >{{ study.completion_percentage }}%</span
                            >
                        </div>
                        <div
                            class="mt-2 h-2 overflow-hidden rounded-full bg-background"
                        >
                            <div
                                class="h-full rounded-full bg-primary"
                                :style="{
                                    width: `${study.completion_percentage}%`,
                                }"
                            ></div>
                        </div>
                        <Button v-if="study.is_editable" class="mt-4" as-child>
                            <Link :href="edit(study.id)">
                                <PencilLine class="size-4" />
                                {{
                                    study.completion_percentage > 0
                                        ? 'متابعة إدخال البيانات'
                                        : 'ابدأ إدخال البيانات'
                                }}
                            </Link>
                        </Button>
                    </div>
                </section>

                <ReportCard
                    v-if="reports.data.length > 0"
                    :reports="reports.data"
                />
                <FinalizeStudyCard
                    v-else-if="finalization"
                    :study-id="study.id"
                    :finalization="finalization"
                />
            </div>

            <PaymentPanel
                v-if="payment"
                class="self-start"
                :study-id="study.id"
                :payment="payment.data"
                :bank="bank"
                :receipt-rules="receiptRules"
            />
        </div>
    </div>
</template>
