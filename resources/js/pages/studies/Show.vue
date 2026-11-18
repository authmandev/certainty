<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarClock, Info } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import PaymentStatusBadge from '@/components/PaymentStatusBadge.vue';
import StudyStatusBadge from '@/components/StudyStatusBadge.vue';
import { formatCurrency, formatDate } from '@/lib/format';
import { dashboard } from '@/routes';
import type { Study } from '@/types';

const props = defineProps<{
    study: { data: Study };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'دراساتي', href: dashboard() }],
    },
});

const study = props.study.data;
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
            <section class="rounded-2xl border bg-card p-6 lg:col-span-2">
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
                        <dt class="text-muted-foreground">آخر موعد للإكمال</dt>
                        <dd class="font-medium">
                            {{ formatDate(study.expires_at) }}
                        </dd>
                    </div>
                    <div v-if="study.days_remaining !== null">
                        <dt class="text-muted-foreground">المدة المتبقية</dt>
                        <dd class="flex items-center gap-1.5 font-medium">
                            <CalendarClock class="size-4" />
                            {{ study.days_remaining }} يومًا
                        </dd>
                    </div>
                </dl>
                <p
                    class="mt-6 rounded-xl bg-muted/60 p-4 text-sm text-muted-foreground"
                >
                    نموذج إدخال بيانات الدراسة (معلومات المشروع، السوق،
                    التكاليف، المبيعات، وتحليل SWOT) سيتوفر هنا قريبًا.
                </p>
            </section>

            <aside
                v-if="study.payment"
                class="space-y-4 rounded-2xl border bg-card p-6 text-sm"
            >
                <h2 class="font-semibold">الدفع</h2>
                <div>
                    <p class="text-muted-foreground">رسوم الدراسة</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums">
                        {{
                            formatCurrency(
                                study.payment.amount,
                                study.payment.currency,
                            )
                        }}
                    </p>
                </div>
                <div>
                    <p class="text-muted-foreground">رقم مرجع الدفع</p>
                    <p class="mt-1 font-medium" dir="ltr">
                        {{ study.payment.reference_number }}
                    </p>
                </div>
                <p class="leading-relaxed text-muted-foreground">
                    اكتب رقم المرجع في وصف التحويل البنكي. ستظهر بيانات الحساب
                    البنكي ورفع الإيصال هنا قريبًا.
                </p>
            </aside>
        </div>
    </div>
</template>
